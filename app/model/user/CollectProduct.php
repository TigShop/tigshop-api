<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 商品收藏
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\user;

use app\model\product\Product;
use app\model\product\ProductSku;
use think\Model;

class CollectProduct extends Model
{
    protected $pk = 'collect_id';
    protected $table = 'collect_product';
    protected $createTime = "add_time";
    protected $autoWriteTimestamp = true;

    // 关联商品
    public function product()
    {
        return $this->hasOne(Product::class, 'product_id', 'product_id')
            ->bind(["product_name", "product_sn", "pic_thumb", "market_price", "is_promote", "promote_price", "promote_start_date", "promote_end_date", "product_stock"]);
    }

    // 关联用户
    public function user()
    {
        return $this->hasOne(User::class, 'user_id', 'user_id')->with(["userRank"])->bind(["username", "rank_id", "discount"]);
    }

    // 关联商品规格属性
    public function productSku()
    {
        return $this->hasMany(ProductSku::class, 'product_id', 'product_id');
    }

    //商品名称检索
    public function scopeProductName($query, $value)
    {
        if (!empty($value)) {
            return $query->hasWhere("product", function ($query) use ($value) {
                $query->whereLike("product_name", "%{$value}%")->where("is_delete", 0);
            });
        }
        return $query;
    }

    // 有效商品
    public function scopeValidProduct($query)
    {
        return $query->hasWhere("product", function ($query) {
            $query->where("is_delete", 0);
        });
    }
}
