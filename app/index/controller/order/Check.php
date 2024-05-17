<?php
//**---------------------------------------------------------------------+
//** 通用接口控制器文件 -- 商品
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\index\controller\order;

use app\index\IndexBaseController;
use app\service\order\OrderCheckService;
use app\service\user\UserAddressService;
use think\App;
use think\response\Json;

/**
 * 商品控制器
 */
class Check extends IndexBaseController
{
    /**
     * 构造函数
     *
     * @param App $app
     */
    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->checkLogin();
    }

    /**
     * 购物车结算
     * @return \think\Response
     * @throws \app\common\exceptions\ApiException
     */
    public function index(): \think\Response
    {
        $orderCheckService = new OrderCheckService();
        $params = [
            'address_id' => $orderCheckService->getSelectedAddressId(),
            'shipping_type' => $orderCheckService->getSelectedShippingType(),
            'pay_type_id' => $orderCheckService->getSelectedPayTypeId(),
            'use_point' => 0,
            'use_balance' => 0,
            'use_coupon_ids' => $orderCheckService->getUseCouponIds(),
        ];
        $orderCheckService->initSet($params);
        $cart_list = $orderCheckService->getStoreCarts('carts');
        if (empty($cart_list)) {
            return $this->error('您还未选择商品！');
        }

        return $this->success([
            'address_list' => app(UserAddressService::class)->getAddressList(request()->userId),
            'available_payment_type' => $orderCheckService->getAvailablePaymentType(),
            'store_shipping_type' => $orderCheckService->getStoreShippingType(),
            'cart_list' => $cart_list,
            'total' => $orderCheckService->getTotalFee(),
            'balance' => $orderCheckService->getUserBalance(),
            'points' => $orderCheckService->getUserPoints(),
            'available_points' => $orderCheckService->getOrderAvailablePoints(),
            'coupon_list' => $orderCheckService->getAvailableCouponList(),
            'item' => $params,
        ]);
    }

    public function update(): \think\Response
    {
        $type = input('type');
        $orderCheckService = new OrderCheckService();
        $params = request()->only([
            'address_id' => 0,
            'shipping_type' => [],
            'pay_type_id' => 1,
            'use_point' => 0,
            'use_balance' => 0,
            'use_coupon_ids' => [],
        ]);
        $orderCheckService->initSet($params);

        return $this->success([
            'store_shipping_type' => $orderCheckService->getStoreShippingType(),
            'available_payment_type' => $orderCheckService->getAvailablePaymentType(),
            'total' => $orderCheckService->getTotalFee(),
            'address_list' => app(UserAddressService::class)->getAddressList(request()->userId),
        ]);
    }

    // 更新优惠券
    public function updateCoupon(): \think\Response
    {
        $orderCheckService = new OrderCheckService();
        $params = request()->only([
            'address_id' => 0,
            'shipping_type' => [],
            'pay_type_id' => 1,
            'use_point' => 0,
            'use_balance' => 0,
            'use_coupon_ids' => [],
        ]);

        if (input('use_default_coupon_ids/d') == 1 && empty($params['use_coupon_ids'])) {
            // 当需要获取默认最优优惠券组合时
            $params['use_coupon_ids'] = $orderCheckService->getUseCouponIds();
        }

        $orderCheckService->initSet($params);

        return $this->success([
            'coupon_list' => $orderCheckService->getAvailableCouponList(),
            'total' => $orderCheckService->getTotalFee(),
        ]);
    }

    // 提交订单
    public function submit(): \think\Response
    {
        $orderCheckService = new OrderCheckService();
        $params = request()->only([
            'address_id' => 0,
            'shipping_type' => [],
            'pay_type_id' => 1,
            'use_point' => 0,
            'use_balance' => 0,
            'use_coupon_ids' => [],
            'buyer_note' => '',
            'invoice_data/a' => [],
        ]);
        $orderCheckService->initSet($params);

        $result = $orderCheckService->submit();
        return $this->success([
            'order_id' => $result['order_id'],
            'return_type' => $result['unpaid_amount'] > 0 ? 1 : 2,
        ]);
    }

    /**
     * 记录发票信息
     * @return \think\Response
     */
    public function getInvoice(): \think\Response
    {
        $orderCheckService = new OrderCheckService();
        $params = request()->only([
            "invoice_type/d" => 0,
            "title_type/d" => 0,
        ]);
        $params["user_id"] = request()->userId;
        $item = $orderCheckService->checkInvoice($params);
        return $this->success([
            'item' => $item,
        ]);
    }

}
