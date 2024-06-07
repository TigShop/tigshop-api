<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 订单统一处理
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\order;

use app\model\authority\AdminUser;
use app\model\order\Aftersales;
use app\model\order\AftersalesItem;
use app\model\order\Order;
use app\model\order\OrderItem;
use app\model\setting\LogisticsCompany;
use app\model\setting\Region;
use app\service\api\admin\product\ProductService;
use app\service\core\BaseService;
use app\validate\order\OrderValidate;
use exceptions\ApiException;
use tig\Http;
use utils\Config;
use utils\Excel;
use utils\Time;

/**
 * 订单服务类
 */
class OrderService extends BaseService
{
    protected Order $orderModel;
    protected OrderValidate $orderValidate;
    protected OrderLogService $orderLogService;
    protected $userId = null;

    public function __construct()
    {
        $this->model = new Order();
    }

    // 设置会员id
    public function setUserId(int | null $user_id)
    {
        $this->userId = $user_id;
        return $this;
    }

    /**
     * 获取筛选结果
     *
     * @param array $filter
     * @return array
     */
    public function getFilterResult(array $filter): array
    {
        $result = $this->getFilterList($filter, ['items', 'user'],
            ['order_status_name', "user_address", "shipping_status_name", "pay_status_name"]);
        foreach ($result as $item) {
            $orderStatusService = new OrderStatusService();
            $item->available_actions = $orderStatusService->getAvailableActions($item);

            foreach ($item->items as $val) {
                $val->aftersales_item = AftersalesItem::where("order_item_id",
                    $val->item_id)->order("aftersales_item_id", "desc")->limit(1)->find();
            }
        }
        return $result->toArray();
    }

    /**
     * 筛选查询
     *
     * @param array $filter
     * @return object
     */
    protected function filterQuery(array $filter): object
    {
        $query = $this->model->query();
        // 处理筛选条件

        // 关键词检索 收货人 + 订单号 + 订单id
        if (isset($filter["keyword"]) && !empty($filter['keyword'])) {
            $query->where(function ($query) use ($filter) {
                $query->where('order_sn', 'like', '%' . $filter['keyword'] . '%')
                    ->whereOr("consignee", 'like', '%' . $filter['keyword'] . '%')
                    ->whereOr("order_id", $filter['keyword']);
            });
        }

        //订单状态
        if (isset($filter["order_status"]) && $filter["order_status"] >= 0) {
            $query->whereIn('order_status',
                is_array($filter['order_status']) ? $filter['order_status'] : [$filter['order_status']]);
        }
        if (isset($filter["order_status"]) && $filter["order_status"] == -2) {
            // 查询删除的订单
            $query->where('is_del', 1);
        } else {
            $query->where('is_del', 0);
        }

        // 店铺检索
        if (isset($filter["shop_id"]) && !empty($filter['shop_id'])) {
            $query->where('shop_id', $filter['shop_id']);
        }

        // 支付状态
        if (isset($filter["pay_status"]) && $filter["pay_status"] != -1) {
            $query->where('pay_status', $filter['pay_status']);
        }

        // 发货状态
        if (isset($filter["shipping_status"]) && $filter["shipping_status"] != -1) {
            $query->where('shipping_status', $filter['shipping_status']);
        }

        // 评价状态
        if (isset($filter["comment_status"]) && $filter["comment_status"] != -1) {
            if ($filter["comment_status"]) {
                $query->where('comment_status', $filter["comment_status"]);
            } else {
                $query->awaitComment();
            }
        }

        // 详情地址
        if (isset($filter["address"]) && !empty($filter['address'])) {
            $query->where('address', 'like', '%' . $filter['address'] . '%');
        }

        // 收货人的email
        if (isset($filter["email"]) && !empty($filter['email'])) {
            $query->where('email', 'like', '%' . $filter['email'] . '%');
        }

        // 手机号
        if (isset($filter["mobile"]) && !empty($filter['mobile'])) {
            $query->where('mobile', 'like', '%' . $filter['mobile'] . '%');
        }

        //配送物流
        if (isset($filter["logistics_id"]) && !empty($filter['logistics_id'])) {
            $query->where('logistics_id', $filter['logistics_id']);
        }

        // 下单时间
        if (isset($filter["add_start_time"], $filter["add_end_time"]) && !empty($filter["add_start_time"]) && !empty($filter["add_end_time"])) {
            $add_time = [$filter["add_start_time"], $filter["add_end_time"]];
            $query->addTime($add_time);
        }

        // 支付时间
        if (isset($filter['pay_time']) && !empty($filter['pay_time'])) {
            $filter['pay_time'] = is_array($filter['pay_time']) ? $filter['pay_time'] : explode(',', $filter['pay_time']);
            list($start_date, $end_date) = $filter['pay_time'];
            $start_date = Time::toTime($start_date);
            $end_date = Time::toTime($end_date) + 86400;
            $pay_time = [$start_date, $end_date];
            $query->whereTime('pay_time', 'between', $pay_time);
        }

        if (isset($filter["user_id"]) && $filter["user_id"] > 0) {
            $query->where('user_id', $filter['user_id']);
        }

        // PC 端 日期检索
        if (isset($filter["date_type"]) && !empty($filter["date_type"])) {
            // 获取时间区间
            $date_range = $this->getDateRange($filter["date_type"]);
            $query->addTime($date_range);
        }

        if (isset($filter["sort_field"], $filter['sort_order']) && !empty($filter['sort_field']) && !empty($filter['sort_order'])) {
            $query->order($filter['sort_field'], $filter['sort_order']);
        }
        return $query;
    }

    /**
     * 获取时间区间
     *
     * @param int $type
     * @return string
     */
    public function getDateRange(int $type): array
    {
        switch ($type) {
            case 1:
                // 最近三个月
                $date_range = Time::format(Time::monthAgo(3), "Y-m-d");
                break;
            case 2:
                // 最近六个月
                $date_range = Time::format(Time::monthAgo(6), "Y-m-d");
                break;
            case 3:
                // 最近一年
                $date_range = Time::format(Time::monthAgo(12), "Y-m-d");
                break;
        }
        return [$date_range, Time::getCurrentDatetime("Y-m-d")];
    }

    /**
     * 获取详情（对象）
     *
     * @param int $id
     * @param int $user_id
     * @return Order
     * @throws ApiException
     */
    public function getOrder(int $id, int | null $user_id = null): Order
    {
        $order = app(OrderDetailService::class)->setId($id)->setUserId($user_id)->getOrder();
        $order->available_actions = app(OrderStatusService::class)->getAvailableActions($order);
        $order->step_status = app(OrderStatusService::class)->getStepStatus($order);

        foreach ($order->items as $value) {
            $value->aftersales_item = AftersalesItem::hasWhere('aftersales', function ($query) {
                $query->whereIn('status', Aftersales::VALID_STATUS);
            })->where("order_item_id",
                $value->item_id)->order("aftersales_item_id", "desc")->limit(1)->find();
            $value->subtotal = $value->price * $value->quantity;
        }
        return $order;
    }

    /**
     * 获取详情（数组）
     *
     * @param int $id
     * @param int $user_id
     * @return array
     * @throws ApiException
     */
    public function getDetail(int $id, int | null $user_id = null): array
    {
        return $this->getOrder($id, $user_id)->toArray();
    }

    /**
     * 获取名称
     *
     * @param int $id
     * @return string|null
     */
    public function getSn(int $id): ?string
    {
        return Order::where('order_id', $id)->value('order_sn');
    }

    /**
     * 获取订单状态
     * @param int $id
     * @return int
     */
    public function getPayStatus(int $id): int
    {
        return Order::where('order_id', $id)->value('pay_status');
    }

    /**
     * 获取订单评价状态
     * @param int $id
     * @return int
     */
    public function getCommentStatus(int $id): int
    {
        return Order::where('order_id', $id)->value('comment_status');
    }

    public function getAddProductInfoByIds(array $ids): ?array
    {
        $filter = [
            'ids' => $ids,
        ];
        $result = app(ProductService::class)->filterQuery($filter)->field('pic_thumb,product_id,product_name,product_type,product_sn,product_price as price')->select();
        return $result->toArray();
    }

    public function creatNewOrderSn(): ?string
    {
        return Time::format(Time::now(), 'YmdHi') . mt_rand(10000000, 99999999);
    }

    // 设置订单已确认
    public function setOrderConfirm(int $id)
    {
        $orderDetail = app(OrderDetailService::class)->setId($id);
        $orderDetail->setOrderConfirm();
        $orderDetail->addLog('设置订单已确认');
    }

    // 拆分订单
    public function splitStoreOrder(int $id)
    {
        $orderDetail = app(OrderDetailService::class)->setId($id);
        $orderDetail->splitStoreOrder();
        $orderDetail->addLog('设置订单已确认');
    }

    // 设置订单已支付
    public function setOrderPaid(int $id)
    {
        $orderDetail = app(OrderDetailService::class)->setId($id);
        $orderDetail->setOrderPaid();
        $orderDetail->addLog('设置订单已支付');
    }

    // 取消订单
    public function cancelOrder(int $id, int $user_id = null)
    {
        $orderDetail = app(OrderDetailService::class)->setId($id)->setUserId($user_id);
        $orderDetail->cancelOrder();
        $orderDetail->addLog('取消订单');
    }

    /**
     * 确认收货
     * @param int $id
     * @param int|null $user_id
     * @return void
     * @throws ApiException
     */
    public function confirmReceipt(int $id, int $user_id = null): void
    {
        $orderDetail = app(OrderDetailService::class)->setId($id)->setUserId($user_id);
        $orderDetail->confirmReceipt();
        $orderDetail->addLog('确认收货');
    }

    // 删除订单
    public function delOrder(int $id, int $user_id = null)
    {
        $orderDetail = app(OrderDetailService::class)->setId($id)->setUserId($user_id);
        $orderDetail->delOrder();
        $orderDetail->addLog('删除订单');
    }

    // 修改订单金额
    public function modifyOrderMoney(int $id, array $data)
    {
        $orderDetail = app(OrderDetailService::class)->setId($id);
        $orderDetail->modifyOrderMoney($data);
        $orderDetail->addLog('更新订单金额');
    }

    // 修改收货人信息
    public function modifyOrderConsignee(int $id, array $data)
    {
        $orderDetail = app(OrderDetailService::class)->setId($id);
        $orderDetail->modifyOrderConsignee($data);
        $orderDetail->addLog('修改订单收货人信息');
    }

    // 修改配送信息
    public function modifyOrderShipping(int $id, array $data)
    {
        $orderDetail = app(OrderDetailService::class)->setId($id);
        $orderDetail->modifyOrderShipping($data);
        $orderDetail->addLog('修改订单配送信息');
    }

    // 修改订单商品
    public function modifyOrderProduct(int $id, array $data)
    {
        $orderDetail = app(OrderDetailService::class)->setId($id);
        $orderDetail->modifyOrderProduct($data);
        $orderDetail->addLog('修改订单商品');
    }

    // 订单发货
    public function deliverOrder(int $id, array $deliver_data, $shipping_method, $logistics_id = 0, $tracking_no = '')
    {
        $orderDetail = app(OrderDetailService::class)->setId($id);
        $orderDetail->deliverOrder($deliver_data, $shipping_method, $logistics_id, $tracking_no);
    }

    // 设置商家备注
    public function setAdminNote(int $id, string $note)
    {
        $orderDetail = app(OrderDetailService::class)->setId($id);
        $orderDetail->setAdminNote($note);
        $orderDetail->addLog('订单商家备注已更新');
    }

    // 打印订单
    public function getOrderPrintInfo(int $id)
    {
        $result = $this->getOrder($id);
        $result = !empty($result) ? $result->toArray() : [];
        unset($result["available_actions"]);
        $result["shop_name"] = Config::get("shop_name");

        $url = isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] === 'http' ? "http://" : "https://";
        $url .= $_SERVER['HTTP_HOST'];
        $result["host"] = $url;
        $result["print_time"] = Time::format();
        return $result;
    }

    /**
     * 订单导出标签列表
     * @return string[]
     */
    public function getExportItemList()
    {
        $export_item_list = [
            "order_sn" => "订单编号",
            "add_time" => "下单时间",
            "order_status_name" => "订单状态",
            "pay_status_name" => "支付状态",
            "store_title" => "店铺名称",
            "username" => "会员名称",
            "consignee" => "收件人姓名",
            "mobile" => "收件人电话",
            "address" => "收件人地址",
            "country" => "国家",
            "total_amount" => "订单总价",
            "balance" => "使用余额",
            "discount_amount" => "折扣",
            "points_amount" => "积分抵金额",
            "coupon_amount" => "优惠券金额",
            "pay_time" => "支付时间",
            "pay_type_id" => "支付类型",
            "paid_amount" => "支付金额",
            "shipping_time" => "发货时间",
            "shipping_fee" => "运费",
            "logistics_name" => "物流名称",
            "tracking_no" => "发货单号",
            "buyer_note" => "订单备注",
            "admin_note" => "商家备注",
            "product_info" => "商品信息",
            "shipping_status_name" => "发货状态",
            "product_weight" => "总重量(KG)",
            "use_points" => "使用积分",
        ];
        return $export_item_list;
    }

    // 保存标签的详情
    public function getExportItemInfo()
    {
        $item = AdminUser::where("admin_id", request()->adminUid)->value("order_export");
        // 获取订单导出标签列表
        $export_info = [];
        $export_list = $this->getExportItemList();
        foreach ($item as $k => $v) {
            if (isset($export_list[$v])) {
                $export_info[$v] = $export_list[$v];
            }
        }
        return $export_info;
    }

    // 保存订单标签
    public function saveExportItem(array $data)
    {
        $result = AdminUser::where("admin_id", request()->adminUid)->save(['order_export' => $data]);
        return $result !== false;
    }

    // 获取订单导出标题
    public function getOrderExportTitle(): array
    {
        // 获取订单标题
        $export_all_list = $this->getExportItemList();
        // 获取要导出的字段
        $export_item = AdminUser::where("admin_id", request()->adminUid)->value("order_export");
        $export_title = [];
        foreach ($export_item as $key => $value) {
            if (isset($export_all_list[$value])) {
                $export_title[] = $export_all_list[$value];
                if ($value == 'product_info') {
                    unset($export_title[$key]);
                }
            }
        }
        if (in_array("product_info", $export_item)) {
            // 商品信息放在最后
            $export_title[] = "商品信息";
        }
        $export_title = array_values($export_title);
        return $export_title;
    }

    // 组装订单导出数据
    public function getOrderExportData(array $data = []): array
    {
        $row = [];
        $product_info = false;
        // 获取要导出的字段
        $export_item = AdminUser::where("admin_id", request()->adminUid)->value("order_export");
        foreach ($export_item as $key => $value) {
            if (isset($data[$value])) {
                $row[$value] = $data[$value];
            }
            if ($value == 'address') {
                // 收件人地址
                $row['address'] = $data["user_address"];
            } elseif ($value == 'country') {
                // 国家
                $country = Config::get("default_country");
                $row["country"] = Region::where("region_id", $country)->value("region_name") ?? "";
            } elseif ($value == 'product_weight') {
                //总重量
                $row["product_weight"] = 0;
                if (!empty($data["items"])) {
                    foreach ($data["items"] as $item) {
                        $row["product_weight"] += $item["product_weight"];
                    }
                }
            } elseif ($value == "product_info") {
                // 商品信息
                unset($export_item[$key]);
                $product_info = true;
            }

        }
        if ($product_info) {
            $row["product_info"] = "";
            foreach ($data["items"] as $k => $item) {
                $product_info = [
                    $item["product_name"], // 商品名称
                    $item["sku_value"], // 商品属性
                    $item["product_sn"], // 商品编号
                    $item["quantity"], // 购买数量
                    $item["price"], // 购买价格
                    $item["price"] * $item["quantity"], // 小计
                ];
                $row["product_info"] .= implode(" | ", $product_info) . "\r\n";
            }

        }
        // 重置下标
        $row = array_values($row);
        return $row;
    }

    // 订单导出
    public function orderExport(array $data): bool
    {
        $export_title = $this->getOrderExportTitle();
        // 组装导出数据
        $export_data = [];
        foreach ($data as $k => $v) {
            $export_data[] = $this->getOrderExportData($v);
        }
        $file_name = "订单导出" . Time::getCurrentDatetime("Ymd") . rand(1000, 9999);
        Excel::export($export_title, $file_name, $export_data);
        return true;
    }

    /**
     * 获取支付订单人数
     * @param array $data
     * @return mixed
     */
    public function getPayOrderUserTotal(array $data): mixed
    {
        return Order::payTime($data)->storePlatform()->paid()->where("is_del", 0)->group("user_id")->count();
    }

    /**
     * 获取下单件数
     * @param array $data
     * @return float
     */
    public function getOrderTotal(array $data): float
    {
        return OrderItem::hasWhere("orders", function ($query) use ($data) {
            $query->storePlatform()->paid()->PayTime($data)->where("is_del", 0);
        })->sum("quantity");
    }

    /**
     * 获取商品支付金额统计
     * @param array $data
     * @return float
     */
    public function getPayMoneyTotal(array $data,int $shopId = 0): float
    {
        return $this->filterQuery([
            'pay_time' => $data,
            'shop_id' => $shopId,
            'pay_status' => Order::PAYMENT_PAID
        ])->sum('total_amount');
    }

    /**
     * 获取商品支付金额统计
     * @param array $data
     * @return mixed
     */
    public function getPayBalanceTotal(array $data,int $shopId = 0)
    {
        return $this->filterQuery([
            'pay_time' => $data,
            'shop_id' => $shopId,
            'order_status' => [Order::ORDER_CONFIRMED, Order::ORDER_PROCESSING,Order::ORDER_COMPLETED]
        ])->sum('balance');
    }

    /**
     * 获取订单金额
     * @param array $data
     * @return array
     */
    public function getPayMoneyList(array $data,int $shopId = 0): array
    {
        return $this->filterQuery([
                'pay_time' => $data,
                'shop_id' => $shopId,
                'pay_status' => Order::PAYMENT_PAID
            ])
            ->field('total_amount,pay_time')
            ->select()->toArray();
    }

    /**
     * 各类型下订单数量
     * @param int $user_id
     * @return array
     */
    public function getOrderQuantity(int $user_id): array
    {
        $result = [];
        $result['await_pay'] = Order::where('user_id', $user_id)->awaitPay()->where("is_del", 0)->count();
        $result['await_shipping'] = Order::where('user_id', $user_id)->awaitShip()->where("is_del", 0)->count();
        $result['await_received'] = Order::where('user_id', $user_id)->awaitReceived()->where("is_del", 0)->count();
        $result['await_comment'] = Order::where('user_id', $user_id)->awaitComment()->where("is_del", 0)->count();
        $result['order_completed'] = Order::where('user_id', $user_id)->Completed()->where("is_del", 0)->count();
        $result['product_collect'] = \app\model\user\CollectProduct::where('user_id', $user_id)->count();
        return $result;
    }

    /**
     * 获得订单物流信息
     * @return mixed
     */
    public function getOrderShipping(int $order_id): mixed
    {
        $url = config('tigshop.api_shipping_url');
        $order_info = Order::find($order_id);
        if (empty($order_info['tracking_no'])) {
            return [];
        }
        $logistics = LogisticsCompany::where('logistics_id', $order_info['logistics_id'])->find();
        $param = [
            'apiKey' => Config::get('api_key'),
            'code' => $logistics['logistics_code'],
            'number' => $order_info['tracking_no'] ?? '',
        ];
        $p_url = $url . http_build_query($param);
        $data = Http::post($p_url);
        $res_arr = json_decode($data, true);
        //@todo 按逻辑加入缓存
        return !empty($res_arr['content']) ? $res_arr['content'] : [];

    }

    /**
     * 再次购买
     * @param int $order_id
     * @return bool
     */
    public function buyAgain(int $order_id, int $user_id): bool
    {
        $itemList = OrderItem::where('order_id', $order_id)->field(['sku_id', 'product_id', 'quantity'])->select();
        if (empty($itemList)) {
            throw new ApiException('订单不存在！');
        }
        $cartService = new CartService();
        $cartService->is_checked($user_id, 0);
        foreach ($itemList as $item) {
            $return = $cartService->addToCart($item['product_id'], $item['quantity'], $item['sku_id']);
            if (!$return) {
                throw new ApiException('再次购买失败！');
            }
        }
        return true;
    }

}
