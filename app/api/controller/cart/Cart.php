<?php
//**---------------------------------------------------------------------+
//** 通用接口控制器文件 -- 购物车
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\api\controller\cart;

use app\api\IndexBaseController;
use app\service\api\admin\order\CartService;
use app\service\api\admin\promotion\CouponService;
use think\App;
use think\Response;

/**
 * 用户购物车控制器
 */
class Cart extends IndexBaseController
{
    /**
     * 构造函数
     *
     * @param App $app
     */
    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    /**
     * 购物车
     *
     * @return Response
     */
    public function list(): Response
    {
        $store_cart = app(CartService::class)->getCartListByStore();
        return $this->success([
            'cart_list' => $store_cart['carts'],
            'total' => $store_cart['total'],
        ]);
    }

    /**
     * 获取购物车商品数量
     *
     * @return Response
     */
    public function getCount(): Response
    {
        $count = app(CartService::class)->getCartCount();
        return $this->success([
            'count' => $count,
        ]);
    }

    /**
     * 更新购物车商品选择状态
     *
     * @return Response
     */
    public function updateCheck(): Response
    {
        $data = input('data/a', []);
        app(CartService::class)->updateCheckStatus($data);
        return $this->success(/** LANG */'购物车更新成功');
    }

    /**
     * 更新购物车商品数量
     *
     * @return Response
     */
    public function updateItem(): Response
    {
        $cart_id = input('cart_id/d', 0);
        $data = input('data/a', []);
        try {
            app(CartService::class)->updateCartItem($cart_id, $data);
        } catch (\Exception $exception) {
            return $this->error(['quantity' => app(CartService::class)->getProductCartNum($cart_id), 'message' => $exception->getMessage()]);
        }
        return $this->success(/** LANG */'购物车更新成功');
    }

    /**
     * 删除购物车商品
     *
     * @return Response
     */
    public function removeItem(): Response
    {
        $cart_ids = input('cart_ids/a', []);
        app(CartService::class)->removeCartItem($cart_ids);
        return $this->success(/** LANG */'购物车商品已移除');
    }

    /**
     * 清空购物车
     *
     * @return Response
     */
    public function clear(): Response
    {
        app(CartService::class)->clearCart();
        return $this->success(/** LANG */ lang('购物车已清空'));
    }

    /**
     * 获得购物车优惠卷折扣信息
     * @return Response
     */
    public function getCouponDiscount(): Response
    {
        $coupon_id = input('coupon_id/d', 0);
        $carts = app(CartService::class)->getCartList(true);
        $coupon = app(CouponService::class)->getDetail($coupon_id);
        $checkedProductPriceSum = 0;
        foreach ($carts as $cart) {
            if ($coupon['send_range'] == 1) {
                if (!in_array($cart['category_id'], $coupon['send_range_data'])) {
                    continue;
                }
            } elseif ($coupon['send_range'] == 2) {
                if (!in_array($cart['brand_id'], $coupon['send_range_data'])) {
                    continue;
                }
            } elseif ($coupon['send_range'] == 3) {
                if (!in_array($cart['product_id'], $coupon['send_range_data'])) {
                    continue;
                }
            } elseif ($coupon['send_range'] == 4) {
                if (in_array($cart['product_id'], $coupon['send_range_data'])) {
                    continue;
                }
            }
            $checkedProductPriceSum = bcadd($cart['product_price'] * $cart['quantity'], $checkedProductPriceSum, 2);
        }
        $discount_money = $coupon['coupon_type'] == 1 ? $coupon['coupon_money'] : bcmul($checkedProductPriceSum,
            $coupon['coupon_discount'] / 10, 2);
        return $this->success([
            'min_order_amount' => $coupon['min_order_amount'],
            'coupon_money' => $coupon['coupon_money'],
            'product_price' => $checkedProductPriceSum,
            'discount_money' => $discount_money,
        ]);
    }
}
