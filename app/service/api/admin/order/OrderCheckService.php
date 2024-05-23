<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 订单结算处理
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\order;

use app\model\finance\OrderInvoice;
use app\model\order\Order;
use app\model\product\Product;
use app\model\setting\ShippingTplInfo;
use app\model\setting\ShippingType;
use app\model\user\User;
use app\model\user\UserCoupon;
use app\service\api\admin\BaseService;
use app\service\api\admin\finance\OrderInvoiceService;
use app\service\api\admin\product\ProductService;
use app\service\api\admin\product\ProductSkuService;
use app\service\api\admin\promotion\SeckillService;
use app\service\api\admin\queue\OrderQueueService;
use app\service\api\admin\setting\ShippingTplService;
use app\service\api\admin\user\UserAddressService;
use app\service\api\admin\user\UserCouponService;
use app\service\api\admin\user\UserService;
use exceptions\ApiException;
use think\facade\Db;
use utils\Config;
use utils\Time;

/**
 * 订单服务类
 */
class OrderCheckService extends BaseService
{

    protected ?array $storeCarts = null;
    protected ?array $address = null;
    protected ?array $regionIds = null;
    protected ?array $cartTotal = null;
    protected ?array $userInfo = null;
    protected ?array $shippingType = null; // 可用的配送方式，按店铺分类
    protected ?array $couponList = null;
    protected ?int $selectedPayTypeId; //已选支付方式
    protected ?array $selectedShippingType; //已选配送类型（按店铺分类）
    protected ?int $selectedAddressId; //已选收货地址ID
    protected ?int $usePoint; //使用积分
    protected ?float $useBalance; //使用余额，此处值为会员当前最大余额，实际使用余额会在total中计算
    protected ?array $useCouponIds;
    protected ?string $buyerNote;
    protected ?array $extension = [];
    protected ?array $invoiceData;

    public function __construct($params = [])
    {
        $this->selectedPayTypeId = null;
        $this->selectedShippingType = null;
        $this->selectedAddressId = null;
        $this->usePoint = null;
        $this->useBalance = null;
        $this->useCouponIds = null;
    }

    public function initSet($params)
    {
        // 设置地址
        $this->setSelectedAddress($params['address_id']);
        // 设置支付方式
        $this->setSelectedPayTypeId($params['pay_type_id']);
        // 设置配送方式
        $this->setSelectedShippingType($params['shipping_type']);
        // 设置使用积分额度
        $this->setUsePoint($params['use_point']);
        // 设置使用余额
        $this->setUseBalance($params['use_balance']);
        // 设置使用优惠券
        $this->setUseCouponIds($params['use_coupon_ids']);
        // 订单备注
        if (isset($params['buyer_note'])) {
            $this->buyerNote = htmlspecialchars($params['buyer_note']);
        }
        //发票信息
        $this->invoiceData = $params['invoice_data'] ?? [];
    }

    public function setSelectedPayTypeId($type_id)
    {
        if (!in_array($type_id, [PaymentService::PAY_TYPE_ONLINE, PaymentService::PAY_TYPE_COD, PaymentService::PAY_TYPE_OFFLINE])) {
            $this->selectedPayTypeId = 0;
        }
        $this->selectedPayTypeId = $type_id;
        return $this;
    }
    // 设置配送方式
    // data : {type_id:类型id，store_id:店铺id}
    public function setSelectedShippingType($data)
    {
        $this->selectedShippingType = $data;
        return $this;
    }

    // 设置地址选中
    public function setSelectedAddress(int $address_id)
    {
        if ($address_id > 0) {
            app(UserAddressService::class)->setAddressSelected(request()->userId, $address_id);
        }
        $this->regionIds = null;
        $this->address = null;
        return $this;
    }

    // 设置使用的余额
    public function setUsePoint($point)
    {
        if ($point > 0) {
            $avalibale_point = $this->getOrderAvailablePoints();
            if ($avalibale_point < $point) {
                throw new ApiException('当前积分不足');
            }
            $this->usePoint = $point;
        } else {
            $this->usePoint = 0;
        }
    }

    // 设置使用的余额
    public function setUseBalance($balance): self
    {
        if ($balance > 0) {
            $user_balance = $this->getUserBalance();
            if ($user_balance < $balance) {
                $balance = $user_balance;
            }
            $this->useBalance = $balance;
        } else {
            $this->useBalance = 0;
        }
        return $this;
    }

    // 设置使用的优惠券
    public function setUseCouponIds(array $couopon_ids)
    {
        $this->useCouponIds = $couopon_ids;
        // 加载列表可以安全过滤一遍优惠券ids
        $this->getAvailableCouponList();
        return $this;
    }

    // 获取购物车选中的商品，按店铺分组
    public function getStoreCarts($data = null): array
    {
        if ($this->storeCarts === null) {
            $this->storeCarts = app(CartService::class)->getCartListByStore(true);
        }
        return $data !== null ? $this->storeCarts[$data] : $this->storeCarts;
    }

    // 支付方式
    public function getSelectedPayTypeId(): int
    {
        if ($this->selectedPayTypeId === null) {
            $payment_type = $this->getAvailablePaymentType();
            $this->selectedPayTypeId = $payment_type[0]['type_id'];
        }
        return $this->selectedPayTypeId;
    }

    public function getAvailablePaymentType(): array
    {
        // 判断shipping_type是否全部支持货到付款
        $result = [
            [
                'type_id' => PaymentService::PAY_TYPE_ONLINE,
                'type_name' => '在线支付',
                'disabled' => false,
                'disabled_desc' => '',
                'is_show' => true,
            ],
//            [
//                'type_id' => PaymentService::PAY_TYPE_COD,
//                'type_name' => '货到付款',
//                'disabled' => !$this->isSupportCod(),
//                'disabled_desc' => '商品或所在地区不支持货到付款',
//                'is_show' => true,
//            ],
            [
                'type_id' => PaymentService::PAY_TYPE_OFFLINE,
                'type_name' => '线下支付',
                'disabled' => false,
                'disabled_desc' => '',
                'is_show' => true,
            ],
        ];
        return $result;
    }

    // 配送方式
    public function getSelectedShippingType(): array
    {
        if ($this->selectedShippingType === null) {
            $shipping_type = $this->getStoreShippingType();
            $this->selectedShippingType = [];
            foreach ($shipping_type as $key => $value) {
                if (isset($value[0])) {
                    // 默认第一个
                    $this->selectedShippingType[$key]['type_id'] = isset($value[0]) ? $value[0]['shipping_type_id'] : 0;
                    $this->selectedShippingType[$key]['store_id'] = isset($value[0]) ? $value[0]['store_id'] : 0;
                    $this->selectedShippingType[$key]['type_name'] = isset($value[0]) ? $value[0]['shipping_type_name'] : '';
                }
            }
        }
        return $this->selectedShippingType;
    }

    // 获取可用的配送方式，按店铺分组
    public function getStoreShippingType(): array
    {
        if ($this->shippingType === null) {
            $cart = $this->getStoreCarts();
            $shipping_type = [];
            $region_ids = $this->getRegionIds();
            foreach ($cart['carts'] as $key => $store) {
                $product_ids = array_unique(array_column($store['carts'], 'product_id'));
                $tpl_ids = $this->getShippingTplIds($store['store_id'], $product_ids);
                $shipping_type[] = $this->getAvailableShippingType($tpl_ids, $region_ids);
            }
            $this->shippingType = $shipping_type;
        }
        return $this->shippingType;
    }

    // 获取选择的地址ID
    public function getSelectedAddressId(): int
    {
        $address = $this->getSelectedAddress();
        $this->selectedAddressId = $address ? $address['address_id'] : 0;
        return $this->selectedAddressId;
    }

    // 获取收货地址
    public function getSelectedAddress(): array
    {
        if ($this->address === null) {
            $this->address = app(UserAddressService::class)->getUserSelectedAddress(request()->userId);
        }
        return $this->address;
    }

    // 获取地区region_ids[number]
    public function getRegionIds(): array
    {
        if ($this->regionIds === null) {
            $region = $this->getSelectedAddress();
            $this->regionIds = isset($region['region_ids']) ? $region['region_ids'] : [];
            //$this->regionIds = [110000];
        }
        return $this->regionIds;
    }

    // 获取店铺下商品的所有运费模板
    public function getShippingTplIds($store_id, $product_ids): array
    {
        $default_id = app(ShippingTplService::class)->getDefaultShippingTplId($store_id);
        $tpl_ids = Product::whereIn('product_id', $product_ids)->column('shipping_tpl_id');
        if (in_array(0, $tpl_ids)) {
            $tpl_ids[] = $default_id;
            $tpl_ids = array_filter($tpl_ids, function ($item) {
                return $item !== 0;
            });
        }
        return $tpl_ids ? array_unique($tpl_ids) : [];
    }

    public function getAvailableShippingType($tpl_ids, $regions): array
    {
        $type_list = [];
        $enabled_tpl_type = [];
        $types = [];
        $idx = 0;
        foreach ($tpl_ids as $key => $tpl_id) {
            if (Config::get('child_area_need_region') == 1) {
                $tpl_info = ShippingTplInfo::where('shipping_tpl_id', $tpl_id)->where('is_default', 0)->field('region_ids,region_names,shipping_type_id')->select();
                if ($tpl_info) {
                    foreach ($tpl_info as $row) {
                        if ($row['area_regions'] && $this->fetchRegion($regions, $row['area_regions'])) {
                            $enabled_tpl_type[] = $row['shipping_type_id'];
                        }
                    }
                }
            }
            $shipping_type_ids = ShippingTplInfo::where('shipping_tpl_id', $tpl_id)->column('DISTINCT shipping_type_id');
            $types = $idx != 0 ? array_intersect($types, $shipping_type_ids) : $shipping_type_ids; //取交集
            $idx++;
        }
        if (Config::get('child_area_need_region') == 1) {
            $type_list = array_intersect($types, $enabled_tpl_type); //取交集，去掉地域不合的模板
        } else {
            $type_list = $types;
        }
        //type_list 按店铺分类的配送类型
        $result = ShippingType::whereIn('shipping_type_id', $type_list)->select();
        return $result->toArray();
    }

    // 获取订单可用的消费积分
    public function getOrderAvailablePoints(): int
    {
        $user = $this->getUserInfo();
        $cart = $this->getStoreCarts();
        $cart_total = $cart['total'];
        $product_amount = $cart_total['product_amount'];
        $avalibale_points = $this->getAmountValuePoints($product_amount);
        return min($avalibale_points, $user['points']);
    }

    public function getUserPoints(): int
    {
        $user = $this->getUserInfo();
        return $user['points'] > 0 ? $user['points'] : 0;
    }

    //计算积分能抵多少金额
    public function getPointValueAmount($point = 0)
    {
        $scale = intval(Config::get('integral_scale'));
        return $scale > 0 ? round(($point / 100) * $scale, 2) : 0;
    }

    //计算金额需要多少积分
    public function getAmountValuePoints($amount = 0)
    {
        $point_scale = Config::get('integral_scale');
        $scale = intval($point_scale);
        return $scale > 0 ? intval($amount / $scale * 100) : 0;
    }

    // 获取用户余额
    public function getUserBalance(): float
    {
        $user = $this->getUserInfo();
        return $user['balance'] > 0 ? $user['balance'] : 0;
    }

    // 是否所有配送方式都支持货到付款
    public function isSupportCod(): bool
    {
        $shipping_type = $this->getStoreShippingType();
        $type = [];
        foreach ($shipping_type as $key => $types) {
            foreach ($types as $_key => $value) {
                if ($value) {
                    $type[$value['shipping_type_id']] = $value['is_support_cod'];
                }
            }
        }
        foreach ($this->getSelectedShippingType() as $key => $value) {
            if (isset($type[$value['type_id']]) && $type[$value['type_id']] == 0) {
                return false;
            }
        }
        return true;
    }

    // 获取用户信息
    public function getUserInfo()
    {
        if ($this->userInfo === null) {
            $user = User::where('user_id', request()->userId)->find();
            if (!$user) {
                throw new ApiException('会员不存在');
            }
            $user = $user->toArray();
            $this->userInfo = $user;
        }
        return $this->userInfo;
    }

    // 获取订单总金额
    public function getTotalFee(): array
    {
        $total = [
            'total_amount' => 0, // '订单总金额（商品总价 + 运费等 - 优惠券 - 各种优惠活动）',
            'paid_amount' => 0, // '已支付金额(包含使用余额+线上支付金额+线下支付金额)',
            'unpaid_amount' => 0, // '未付款金额（计算方式为：total_amount - paid_amount）',
            'unrefund_amount' => 0, // '未退款金额（一般出现在订单取消或修改金额、商品后）',
            'coupon_amount' => 0, // '使用优惠券金额 ',
            'points_amount' => 0, // '使用积分金额',
            'balance' => 0, // '使用余额金额',
            'service_fee' => 0, // '服务费',
            'shipping_fee' => 0, // '配送费用',
            'invoice_fee' => 0, // '发票费用',
            'discount_amount' => 0, // '各种优惠活动金额，如折扣、满减等',
            'store_shipping_fee' => [],
        ];
        $cart = $this->getStoreCarts();
        $cart_total = $cart['total'];

        //商品总金额
        $total['product_amount'] = $cart_total['product_amount'];

        // 优惠券
        $coupons = $this->getAvailableCouponList();
        $coupon_amount = 0;
        foreach ($coupons as $store) {
            foreach ($store as $row) {
                if ($row['selected']) {
                    $coupon_amount = bcadd($row['coupon_money'], $coupon_amount, 2);
                    if ($row['is_global']) {
                        $row['store_id'] = -1;
                    }
                    //全局优惠券store_id用-1表达
                    // 用于在订单中记录优惠券的信息
                    $this->extension['coupon_money'][$row['store_id']] = ($this->extension['coupon_money'][$row['store_id']] ?? 0) + $row['coupon_money'];
                    $this->extension['coupon'][$row['store_id']][] = $row['id'];
                }
            }
        }
        //全场券有且只能使用一张,非全场券各个店铺只能选用一张
        if (isset($this->extension['coupon'])) {
            foreach ($this->extension['coupon'] as $key => $value) {
                if (count($value) > 1) {
                    if ($key == -1) {
                        throw new ApiException('优惠券信息错误，全场券有且只能使用一张！');
                    } else {
                        throw new ApiException('优惠券信息错误，非全场券各店铺有且只能使用一张！');
                    }
                }
            }
        }

        $total['coupon_amount'] = $coupon_amount;

        // 积分
        $point = $this->usePoint;
        $total['points_amount'] = $this->getPointValueAmount($point);

        // 运费
        $shipping_fee = $this->getShippingFee();
        $total['shipping_fee'] = $shipping_fee['total'];
        $this->extension['shipping_fee'] = $shipping_fee['store_shipping_fee']; // 用于在订单中记录店铺相关的运费信息
        $total['store_shipping_fee'] = $shipping_fee['store_shipping_fee'];

        // 配送方式
        foreach ($this->getSelectedShippingType() as $value) {
            $this->extension['shipping_type'][$value['store_id']] = [
                'type_id' => $value['type_id'],
                'type_name' => $value['type_name'],
            ];
        }

        // 计算总费用
        $total_amount = round($total['product_amount'] + $total['shipping_fee'] - $total['points_amount'] - $total['coupon_amount'] - $total['discount_amount'], 2);
        $total['total_amount'] = $total_amount > 0 ? $total_amount : 0;

        // 余额
        if ($this->useBalance) {
            $user_balance = $this->getUserBalance();
            if ($total['total_amount'] < $user_balance) {
                $this->setUseBalance($total['total_amount']);
                $user_balance = $total['total_amount'];
            }
            $total['balance'] = $total['total_amount'] - $user_balance > 0 ? round($total['total_amount'] - $user_balance) : $user_balance;
        }

        $total['unpaid_amount'] = $total['total_amount'] - $total['balance'];

        return $total;
    }

    //根据重量或者件数计算运费
    public function getShippingFee()
    {
        $cart = $this->getStoreCarts();
        $carts = $cart['carts'];
        $cart_total = $cart['total'];
        $data = [];
        foreach ($carts as $key => $store) {
            $default_tpl_id = app(ShippingTplService::class)->getDefaultShippingTplId($store['store_id']);
            foreach ($store['carts'] as $_key => $value) {
                $shipping_tpl_id = $value['shipping_tpl_id'] > 0 ? $value['shipping_tpl_id'] : $default_tpl_id;
                if (!isset($data[$value['store_id']][$shipping_tpl_id])) {
                    $data[$value['store_id']][$shipping_tpl_id] = [
                        'weight' => 0,
                        'count' => 0,
                        'fee' => 0,
                    ];
                }
                if (!$value['free_shipping']) {
                    // 不计算包邮的商品数量和重量
                    $data[$value['store_id']][$shipping_tpl_id]['weight'] += $value['product_weight'];
                    $data[$value['store_id']][$shipping_tpl_id]['count'] += $value['quantity'];
                }
            }
        }
        $selected_type_ids = [];
        foreach ($this->getSelectedShippingType() as $key => $value) {
            $selected_type_ids[$value['store_id']] = $value['type_id'];
        }
        $result = [
            'total' => 0,
            'store_shipping_fee' => [],
        ];
        foreach ($data as $store_id => $row) {
            $type_id = $selected_type_ids[$store_id] ?? 0;
            $tpl_info = [];
            $all_tpl_info = ShippingTplInfo::where('shipping_type_id', $type_id)->select();
            $all_tpl_info = $all_tpl_info ? $all_tpl_info->toArray() : [];
            foreach ($all_tpl_info as $key => $value) {
                if ($value['is_default']) {
                    $tpl_info = $value;
                } else {
                    if ($this->fetchRegion($this->getRegionIds(), $value['region_data']['area_regions'])) {
                        $tpl_info = $value;
                    }
                }
            }
            foreach ($row as $shipping_tpl_id => $value) {
                if ($tpl_info) {
                    // 首件或首重金额
                    $data[$store_id][$shipping_tpl_id]['fee'] = $value['count'] > 0 ? $tpl_info['start_price'] : 0;
                    if ($tpl_info['pricing_type'] == 1) {
                        //按件计费
                        if ($value['count'] - $tpl_info['start_number'] > 0) {
                            $count = @intval(($value['count'] - $tpl_info['start_number']) / $tpl_info['add_number']); //取整，不四舍五入
                            $data[$store_id][$shipping_tpl_id]['fee'] += $count * $tpl_info['add_price'];
                        }
                    } elseif ($tpl_info['pricing_type'] == 2) {
                        //按重量计费
                        if ($value['weight'] - $tpl_info['start_number'] > 0) {
                            $weight = @intval(($value['weight'] - $tpl_info['start_number']) / $tpl_info['add_number']) + 1; //取整，不四舍五入
                            $data[$store_id][$shipping_tpl_id]['fee'] += $weight * $tpl_info['add_price'];
                        }
                    }
                    $result['total'] += $data[$store_id][$shipping_tpl_id]['fee'];
                    if (!isset($result['store_shipping_fee'][$store_id])) {
                        $result['store_shipping_fee'][$store_id] = 0;
                    }
                    $result['store_shipping_fee'][$store_id] += $data[$store_id][$shipping_tpl_id]['fee'];
                }
            }

        }
        return $result;
    }

    // 匹配地区
    protected function fetchRegion(array $region, array $regions)
    {
        for ($i = 0; $i <= count($region); $i++) {
            $current_regions = array_slice($region, 0, $i + 1);

            if (in_array($current_regions, $regions)) {
                return true;
            }
        }
        return false;
    }

    // 优惠券
    public function getUseCouponIds()
    {
        if ($this->useCouponIds === null) {
            // 加载列表即可初使化优惠券ids
            $this->getAvailableCouponList();
        }
        return $this->useCouponIds;
    }

    // 获取可用的优惠券列表
    public function getAvailableCouponList(): array
    {
        if ($this->couponList === null) {
            $cart = $this->getStoreCarts();
            $carts = $cart['carts'];
            $cart_total = $cart['total'];
            $coupons = [];
            $all_product_ids = [];
            $all_product_id_amounts = [];
            foreach ($carts as $key => $store) {
                $product_amount = 0;
                $product_ids = [];
                $product_id_amounts = []; //按商品id归类的金额
                foreach ($store['carts'] as $_key => $value) {
                    $product_amount += $value['subtotal'];
                    $product_ids[] = $value['product_id'];
                    if (isset($product_id_amounts[$value['product_id']])) {
                        $product_id_amounts[$value['product_id']] += $value['subtotal'];
                    } else {
                        $product_id_amounts[$value['product_id']] = 0;
                    }
                }
                $coupons[] = $this->getStoreCouponList(request()->userId, $store['store_id'], $product_amount, false, $product_ids, $product_id_amounts);
                $all_product_id_amounts = array_merge($all_product_id_amounts, $product_id_amounts);
                $all_product_ids = array_merge($all_product_ids, $product_ids);
            }
            // 添加全局券
            $global_coupons = $this->getStoreCouponList(request()->userId, 0, $cart_total['product_amount'], true, $all_product_ids, $all_product_id_amounts);
            if ($global_coupons) {
                $coupons = array_merge([$global_coupons], $coupons);
            }
            // 重新归类
            $result = [
                'enable_coupons' => [],
                'disable_coupons' => [],
            ];
            $selected_coupon_ids = [];
            foreach ($coupons as $key => $value) {
                foreach ($value as $_key => $row) {
                    if ($row['disabled']) {
                        $result['disable_coupons'][] = $row;
                    } else {
                        $result['enable_coupons'][] = $row;
                        if ($row['selected']) {
                            $selected_coupon_ids[] = $row['id'];
                        }

                    }
                }
            }
            $this->useCouponIds = $selected_coupon_ids;
            $this->couponList = $result;
        }
        return $this->couponList;
    }
    //

    /**
     * 获取店铺的优惠券
     *
     * @param [type] $user_id
     * @param integer $store_id
     * @param integer $product_amount 商品总金额
     * @param boolean $is_global 是否全局
     * @param array $product_ids 商品id
     * @param array $product_id_amounts 商品金额 [[id=>amount]]
     * @return array
     */
    protected function getStoreCouponList($user_id, $store_id = 0, $product_amount = 0, $is_global = false, $product_ids = [], $product_id_amounts = []): array
    {

        $now = Time::now();
        $selected = false; //该店铺是否有已选择的优惠券
        $coupon = UserCoupon::withJoin('coupon')
            ->where('user_id', request()->userId)
            ->where('store_id', $store_id)
            ->where('is_global', $is_global)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->where('order_id', 0)
            ->where('used_time', 0)
            ->select()
            ->toArray();
        $max_money_coupon_key = null;
        $result = [];
        foreach ($coupon as $key => $value) {
            $value['disabled'] = false;
            $value['disable_reason'] = '';
            $value['selected'] = false;
            $range_ids = $value['send_range_data'];
            if ($value['coupon']['send_range'] == 1) {
            }
            if ($value['coupon']['send_range'] == 3) {
                // 只包含指定商品
                if (!array_intersect($product_ids, $range_ids)) {
                    $value['disabled'] = true;
                    $value['disable_reason'] = /** LANG */
                    '所结算商品中没有指定的商品';
                }
                $product_amount = 0;
                foreach ($product_id_amounts as $product_id => $amount) {
                    if (in_array($product_id, $range_ids)) {
                        $product_amount += $amount;
                    }

                }
            } elseif ($value['coupon']['send_range'] == 4) {
                // 排除指定商品
                if (!array_diff($product_ids, $range_ids)) {
                    $value['disabled'] = true;
                    $value['disable_reason'] = /** LANG */
                    '所结算商品中没有指定的商品';
                }
                foreach ($product_id_amounts as $product_id => $amount) {
                    if (in_array($product_id, $range_ids)) {
                        $product_amount -= $amount;
                    }
                }
            }
            if ($value['coupon_type'] == 2) {
                // 折扣券
                $coupon[$key]['coupon_money'] = $value['coupon_money'] = round($product_amount - round($product_amount * $value['coupon_discount'] * 10 / 100, 2), 2);
            }

            // 标记已选优惠券
            $use_coupon_ids = $this->useCouponIds ? $this->useCouponIds : [];
            if (in_array($value['id'], $use_coupon_ids)) {
                $value['selected'] = true;
                $selected = true;
            }

            // 判断金额未达到的优惠券并标记disabled
            if ($value['disabled'] == false && $value['coupon']['min_order_amount'] > $product_amount) {
                $value['disabled'] = true;
                $value['disable_reason'] = /** LANG */
                '指定商品差' . $value['coupon']['min_order_amount'] . '可用该券';
            }

            // 记录可选优惠券里最大金额的优惠券，以供默认选择最大金额的
            if ($value['disabled'] == false) {
                if ($max_money_coupon_key === null) {
                    $max_money_coupon_key = $key;
                } else {
                    if ($value['coupon_money'] > $coupon[$max_money_coupon_key]['coupon_money']) {
                        $max_money_coupon_key = $key;
                    }
                }
            }
            $result[] = [
                'id' => $value['id'],
                'coupon_name' => $value['coupon']['coupon_name'],
                'coupon_type' => $value['coupon']['coupon_type'],
                'min_order_amount' => $value['coupon']['min_order_amount'],
                'coupon_desc' => $value['coupon']['coupon_desc'],
                'coupon_money' => $value['coupon_money'],
                'is_global' => $value['is_global'],
                'coupon_discount' => $value['coupon_discount'],
                'store_id' => $value['coupon']['store_id'],
                'end_date' => $value['end_date'],
                'coupon_id' => $value['coupon_id'],
                'disable_reason' => $value['disable_reason'],
                'disabled' => $value['disabled'],
                'selected' => $value['selected'],
            ];
        }
        // 当有可选优惠券且店铺未选时，选择最大的
        if ($result && $selected == false && $max_money_coupon_key !== null && $this->useCouponIds === null) {
            $result[$max_money_coupon_key]['selected'] = true;
        }
        // 按金额大小排序
        if ($result) {
            array_multisort(array_column($result, 'coupon_money'), SORT_DESC, $result);
        }
        return $result;
    }

    public function submit()
    {
        $cart = $this->getStoreCarts();
        $carts = $cart['carts'];
        $cart_total = $cart['total'];
        $item_data = [];
        $cart_ids = [];
        foreach ($carts as $key => $store) {
            foreach ($store['carts'] as $value) {
                if ($value['product_status'] == 0) {
                    throw new ApiException('购物清单中存在已下架商品，请刷新重试');
                }
                if ($value['stock'] == 0) {
                    throw new ApiException('购物清单中存在库存不足商品，请刷新重试');
                }
                $cart_ids[] = $value['cart_id'];
                $item_data[] = [
                    'user_id' => request()->userId, //会员id
                    'price' => $value['price'], //商品最终单价
                    'quantity' => $value['quantity'], // 商品的购买数量
                    'product_id' => $value['product_id'], //商品的的id
                    'product_name' => $value['product_name'], // 商品的名称
                    'product_sn' => $value['product_sn'], // 商品的唯一货号
                    'pic_thumb' => $value['pic_thumb'], // 商品缩略图
                    'sku_id' => $value['sku_id'], //规格ID
                    'sku_data' => $value['sku_data'], //购买该商品时所选择的属性
                    'product_type' => $value['product_type'], // 是否是实物
                    'store_id' => $value['store_id'], //店铺id
                    'type' => $value['type'], //商品类型
                    // 'prepay_price' => $value['prepay_price'], // 预售价格todo
                ];
            }
        }
        $orderService = new OrderService();
        $userService = app(userService::class);
        $address = $this->getSelectedAddress();
        $total = $this->getTotalFee();
        // 扩展信息
        $data = [
            'order_sn' => $orderService->creatNewOrderSn(), //订单号,唯一
            'user_id' => request()->userId, //用户id,同users的user_id
            'order_status' => Order::ORDER_PENDING, //订单的状态
            'shipping_status' => Order::SHIPPING_PENDING, //商品配送情况
            'pay_status' => Order::PAYMENT_UNPAID, //支付状态
            'add_time' => Time::now(), //订单生成时间
            'consignee' => $address['consignee'], //收货人的姓名
            'region_ids' => $address['region_ids'], //[JOSN]地区id数组
            'region_names' => $address['region_names'], //[JOSN]地区name数组
            'address' => $address['address'], //存JSON
            'mobile' => $address['mobile'], //收货人的手机
            'email' => $address['email'], //收货人的Email
            'buyer_note' => $this->buyerNote, //买家备注
            'pay_type_id' => $this->getSelectedPayTypeId(), //支付类型
            'use_points' => $this->usePoint, //使用的积分的数量
            'promoter_user_id' => 0, //推广人userId
            'store_id' => 0, //店铺id
            'store_title' => '', //店铺名称
            'is_store_splited' => 0, //是否已按店铺拆分:0,否;1,是
            'total_amount' => $total['total_amount'], //订单总金额（商品总价 + 运费等 - 优惠券 - 各种优惠活动）
            'paid_amount' => $total['paid_amount'], //已支付金额(包含使用余额+线上支付金额+线下支付金额)
            'unpaid_amount' => $total['unpaid_amount'], //未付款金额（计算方式为：total_amount - paid_amount）
            'product_amount' => $total['product_amount'], //商品的总金额
            'coupon_amount' => $total['coupon_amount'], //使用优惠券金额
            'points_amount' => $total['points_amount'], //使用积分金额
            'balance' => $total['balance'], //使用余额金额
            'service_fee' => $total['service_fee'], //服务费
            'shipping_fee' => $total['shipping_fee'], //配送费用
            'invoice_fee' => $total['invoice_fee'], //发票费用
            'discount_amount' => $total['discount_amount'], //各种优惠活动金额，如折扣、满减等 todo
            'order_extension' => $this->extension, //[JSON]记录订单使用的优惠券、优惠活动、不同店铺配送等的具体细节信息
            'order_source' => \utils\Util::getUserAgent(), //下单来源设备，APP|PC|H5|微信公众号|微信小程序
        ];
        if (count($carts) === 1) {
            // 所有商品都是来自同一店铺时
            $data['is_store_splited'] = 1;
            $data['store_id'] = $carts[0]['store_id'];
            // 更新订单配送类型
            $shipping_type = $this->getSelectedShippingType();
            $data['shipping_type_id'] = $shipping_type[0]['type_id'];
            $data['shipping_type_name'] = $shipping_type[0]['type_name'];
        }

        $order = new Order();
        // 检查订单编号是否已存在
        while ($order->where('order_sn', $data['order_sn'])->find()) {
            $data['order_sn'] = $orderService->creatNewOrderSn();
        }
        try {
            // 启动事务
            Db::startTrans();
            // 创建订单
            $order->save($data);
            if (empty($data['unpaid_amount'])) {
                app(OrderService::class)->setOrderPaid($order->order_id);
            }
            //使用了积分减积分
            if ($data['use_points'] > 0) {
                $userService->decPoints($data['use_points'], $data['user_id'], '订单支付扣除积分');
            }
            //使用了余额减余额
            if ($data['balance'] > 0) {
                $userService->decBalance($data['balance'], $data['user_id'], '订单支付扣除余额');
            }
            //减购物车
            if (!empty($cart_ids)) {
                app(CartService::class)->removeCartItem($cart_ids);
            }
            //设置优惠券已使用
            if ($data['coupon_amount'] > 0) {
                app(UserCouponService::class)->useCoupon($this->useCouponIds, $data['user_id'], $order->order_id);
            }
            // 添加订单商品
            foreach ($item_data as $key => $value) {
                $item_data[$key]['order_id'] = $order->order_id;
                $item_data[$key]["order_sn"] = $order->order_sn;
                //减库存
                if (!empty($value['sku_id'])) {
                    app(ProductSkuService::class)->decStock($value['sku_id'], $value['quantity']);
                } else {
                    app(ProductService::class)->decStock($value['product_id'], $value['quantity']);
                }
                //增加秒杀销量
                app(SeckillService::class)->incSales($value['product_id'], $value['sku_id'], $value['quantity']);
                //增加商品销量
                app(ProductService::class)->incSales($value['product_id'], $value['quantity']);
            }
            $order->items()->saveAll($item_data);

            $orderDetailService = app(OrderDetailService::class)->setId($order->order_id);
            $orderDetailService->addLog(/** LANG */ '会员提交订单');

            // 添加发票申请
            if (!empty($this->invoiceData)) {
                $invoice_data = $this->invoiceData;
                $invoice_data["user_id"] = request()->userId;
                $invoice_data["status"] = 0;
                $invoice_data["order_id"] = $order->order_id;

                if (OrderInvoice::create($invoice_data)) {
                    $order->invoice_data = $invoice_data;
                    $order->save();
                }
            }
            if ($data['unpaid_amount'] > 0 && $data['pay_type_id'] != PaymentService::PAY_TYPE_OFFLINE) {
                //加入队列
                app(OrderQueueService::class)->cancelUnPayOrderJob($order->order_id);
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw new ApiException($e->getMessage());
        }
        return [
            'order_id' => $order->order_id,
            'order_sn' => $order->order_sn,
            'unpaid_amount' => $data['unpaid_amount'],
        ];
    }

    /**
     * 记录发票信息
     * @param array $params
     * @return mixed
     */
    public function checkInvoice(array $params): mixed
    {
        $query = app(OrderInvoiceService::class)->filterQuery($params);
        $result = $query->where("status", 1)->order("id", "desc")->limit(1)->findOrEmpty()->toArray();
        return !empty($result) ? $result : false;
    }
}
