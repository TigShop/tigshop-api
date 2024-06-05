<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 购物车
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\order;

use app\model\order\Cart;
use app\service\api\admin\product\ProductDetailService;
use app\service\api\admin\product\ProductPriceService;
use app\service\api\admin\product\ProductSkuService;
use app\service\api\admin\product\ProductStockService;
use app\service\api\admin\user\UserRankService;
use app\service\api\admin\user\UserService;
use app\service\core\BaseService;
use exceptions\ApiException;
use utils\Time;

/**
 * 购物车服务类
 */
class CartService extends BaseService
{
    public function __construct()
    {
    }

    /**
     * 加入购物车
     *
     * @param integer $id
     * @param integer $number
     * @param integer $sku_id
     * @param [type] $type
     * @param boolean $is_quick 快速购买
     * @return boolean
     */
    public function addToCart(int $id, int $number, int $sku_id, bool $is_quick = false, int $type = Cart::TYPE_NORMAL): bool
    {
        $productDetailService = new ProductDetailService($id);
        $product = $productDetailService->getDetail();
        if ($product['product_status'] == 0) {
            throw new ApiException(/** LANG */'该商品已下架！');
        }
        if ($number <= 0) {
            throw new ApiException(/** LANG */'商品数量错误！');
        }
        $sku = $productDetailService->getProductSkuDetail($sku_id);
        $item = Cart::where('product_id', $id)->where('user_id', request()->userId)->where('sku_id', $sku['id'])->where('type', $type)->find();
        if ($item) {
            if (!$is_quick) {
                $number += $item->quantity;
            }
        }
        $data = [
            'user_id' => request()->userId,
            'product_id' => $id,
            'product_sn' => $product['product_sn'],
            'pic_thumb' => $product['pic_thumb'],
            'market_price' => $product['market_price'],
            'quantity' => $number,
            'original_price' => $sku['price'],
            'sku_id' => $sku['id'],
            'sku_data' => $sku['data'],
            'product_type' => $product['product_type'],
            'is_checked' => 1,
            'type' => $type,
            'shop_id' => $product['shop_id'],
            'update_time' => Time::now(),
        ];
        // 检查库存
        if (app(ProductStockService::class)->checkProductStock($data['quantity'], $id, $sku_id) === false) {
            throw new ApiException(/** LANG */'商品:' . $product['product_name'] . '库存不足！');
        }
        if ($is_quick) {
            Cart::where('user_id', request()->userId)->where('type', $type)->update(['is_checked' => 0]);
        }
        if ($item) {
            // 更新购物车
            Cart::where('cart_id', $item->cart_id)->save($data);
        } else {
            Cart::create($data);
        }
        return true;
    }

    // 获取购物车，返回按店铺分类
    public function getCartListByStore($is_checked = false)
    {
        $cart_list = $this->getCartList($is_checked);
        $carts = [];
        $total = [
            'product_amount' => 0, //商品价格（已选）
            'checked_count' => 0, //商品数（已选）
            'discounts' => 0, //优惠 组合商品（已选）
            'total_count' => 0, //总商品数
        ];
        $user_rank_id = app(UserService::class)->getUserRankId(request()->userId);
        $ranks_list = app(UserRankService::class)->getUserRankList();
        foreach ($cart_list as $key => $row) {
            $row['is_checked'] = $row['is_checked'] == 1 ? true : false;
            $row['price'] = app(ProductPriceService::class)->getProductFinalPrice($row['product_id'], $row['product_price'], $row['sku_id'], $user_rank_id, $ranks_list);
            $row['stock'] = app(ProductStockService::class)->getProductStock($row['product_id'], $row['sku_id']);
            $row['has_sku'] = app(ProductSkuService::class)->checkProductHasSku($row['product_id']);
            $row['subtotal'] = $row['quantity'] * $row['price'];
            $row['is_disabled'] = false;
            if ($row['stock'] == 0 || $row['product_status'] == 0) {
                $row['is_disabled'] = true;
                //重置购物车选中状态
                if ($row['is_checked']){
                    $this->updateCartCheckStatus($row['cart_id']);
                    $row['is_checked'] = false;
                }
            }
            if ($row['has_sku'] && empty($row['sku_id'])) {
                $row['stock'] = 0;
                $row['is_disabled'] = true;
                //重置购物车选中状态
                if ($row['is_checked']){
                    $this->updateCartCheckStatus($row['cart_id']);
                    $row['is_checked'] = false;
                }
                $row['stock'] = 0;
            }
            $total['total_count'] += $row['quantity'];
            if ($row['is_checked']) {
                $total['checked_count'] += $row['quantity'];
                $total['product_amount'] += $row['subtotal'];
            }
            $carts[$row['shop_id']]['shop_id'] = $row['shop_id'];
            $carts[$row['shop_id']]['shop_title'] = !empty($row['shop']) ? $row['shop']['shop_title'] : '';
            $carts[$row['shop_id']]['carts'][] = $row;
        }
        // 将结果转换为索引数组
        $carts = array_values($carts);
        return [
            'carts' => $carts,
            'total' => $total,
        ];
    }

    // 获取购物车
    public function getCartList($is_checked = false): array
    {
        $cart_list = Cart::where('user_id', request()->userId)
            ->when($is_checked == true, function ($query) {
                $query->where('is_checked', true);
            })
            ->hasWhere('product')
            ->with(['shop', 'product'])
            ->order('update_time', 'desc')
            ->select();
        return $cart_list ? $cart_list->toArray() : [];
    }

    // 获取购物车数量
    public function getCartCount(): int
    {
        $quantity = Cart::where('user_id', request()->userId)->sum('quantity');
        return $quantity > 0 ? $quantity : 0;
    }

    /**
     * 获取指定购物车的数量
     * @param int $cart_id
     * @return int
     */
    public function getProductCartNum(int $cart_id): int
    {
        $quantity = Cart::where('cart_id', $cart_id)->value('quantity');

        return $quantity ?? 0;
    }

    /**
     * 更新购物车项
     *
     * @param int $cart_id
     * @param array $arr 数组:
     *                    - 'cart_id': 购物车id
     *                    - 'is_checked': 是否选中
     * */
    public function updateCheckStatus(array $arr)
    {
        $data = [];
        $existing_ids = Cart::where('user_id', request()->userId)->column('cart_id');
        foreach ($arr as $key => $value) {
            if (in_array($value['cart_id'], $existing_ids)) {
                $data[] = [
                    'cart_id' => intval($value['cart_id']),
                    'is_checked' => $value['is_checked'] == 1 ? 1 : 0,
                ];
            }
        }
        $cartModel = new Cart();
        $result = $cartModel->saveAll($data);
        return true;
    }

    /**
     * 更新购物车项
     *
     * @param int $cart_id
     * @param array $arr 数组:
     *                    - 'quantity': 更新的数量
     * @throws ApiException
     */
    public function updateCartItem(int $cart_id, array $arr)
    {
        $data = [
            'quantity' => intval($arr['quantity']),
        ];
        $cart = Cart::where('user_id', request()->userId)->find($cart_id);
        if ($cart) {
            //检测商品库存是否充足
            $stock = app(ProductStockService::class)->getProductStock($cart->product_id, $cart->sku_id);
            if ($stock < intval($arr['quantity'])) {
                throw new ApiException(/** LANG */'库存不足！');
            }
            $cart->save($data);
            return true;
        } else {
            throw new ApiException(/** LANG */'购物车不存在此商品');
        }
    }

    /**
     * 删除购物车商品
     *
     * @param int $id
     * @return bool
     */
    public function removeCartItem(array $cart_ids): bool
    {
        if (!$cart_ids) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = Cart::where('user_id', request()->userId)->whereIn('cart_id', $cart_ids)->delete();
        return $result !== false;
    }

    /**
     * 清空购物车
     *
     * @return bool
     */
    public function clearCart(): bool
    {
        $result = Cart::where('user_id', request()->userId)->delete();
        return $result !== false;
    }

    /**
     * 设置用户购物车选中状态
     * @return bool
     */
    public function is_checked(int $user_id, int $checked): bool
    {
        $result = Cart::where('user_id', $user_id)->update([
            'is_checked' => $checked,
        ]);
        return $result !== false;
    }

    /**
     * 更新购物车状态
     * @param int $cart_id
     * @param int $checked
     * @return void
     */
    public function updateCartCheckStatus(int $cart_id, int $checked = 0): void
    {
        Cart::where('cart_id', $cart_id)->update(['is_checked' => $checked]);
    }
}
