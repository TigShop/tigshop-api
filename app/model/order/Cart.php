<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 购物车
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\order;

use app\model\product\Product;
use app\model\shop\Shop;
use think\Model;

class Cart extends Model
{
    protected $pk = 'cart_id';
    protected $table = 'cart';
    protected $json = ['sku_data'];
    protected $jsonAssoc = true;
    const TYPE_NORMAL = 1; //普通商品
    const TYPE_PIN = 2; //拼团商品
    const TYPE_EXCHANGE = 3; //兑换商品

    // 关联 shop 表
    public function shop()
    {
        return $this->hasOne(Shop::class, 'shop_id', 'shop_id');
    }

    public function product()
    {
        return $this->hasOne(Product::class, 'product_id', 'product_id')->bind(['product_weight', 'shipping_tpl_id', 'free_shipping', 'product_status', 'product_name', 'product_price']);
    }
}
