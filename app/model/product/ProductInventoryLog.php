<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 商品库存日志
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\product;

use think\Model;
use utils\Time;

class ProductInventoryLog extends Model
{
    protected $pk = 'log_id';
    protected $table = 'product_inventory_log';
    protected $createTime = "add_time";
    protected $autoWriteTimestamp = true;

    // 商品关联
    public function product()
    {
        return $this->hasOne(Product::class, 'product_id', 'product_id')->bind(["product_name"]);
    }

    public function getAddTimeAttr($value)
    {
        return Time::format($value);
    }

    // 商品名称检索
    public function scopeProductName($query, $value)
    {
        return $query->hasWhere('product', function ($query) use ($value) {
            $query->where('product_name', 'like', '%' . $value . '%');
        });
    }
}
