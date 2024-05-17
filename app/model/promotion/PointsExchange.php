<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 积分商品
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\promotion;

use app\model\product\Product;
use think\Model;

class PointsExchange extends Model
{
    protected $pk = 'id';
    protected $table = 'points_exchange';

    public function product()
    {
        return $this->hasOne(Product::class, 'product_id', 'product_id')->bind(['product_name', "market_price", "pic_url", "virtual_sales"]);
    }

    //商品名称检索
    public function scopeProductName($query, $value)
    {
        return $query->hasWhere("product", function ($query) use ($value) {
            return $query->where('product_name', 'like', '%' . $value . '%');
        });
    }

    public function scopeIsHot($query, $value): void
    {
        $query->where($this->table . '.is_hot', $value);

    }
}
