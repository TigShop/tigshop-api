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

namespace app\index\controller\cart;

use app\index\IndexBaseController;
use app\service\order\CartService;
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
        $this->checkLogin();
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
        return $this->success(/** LANG */'购物车已清空');
    }
}
