<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 商品规格
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\product;

use think\Model;

class ProductSku extends Model
{
    protected $pk = 'sku_id';
    protected $table = 'product_sku';
    protected $json = ['sku_data'];
    // 设置JSON数据返回数组
    protected $jsonAssoc = true;

    public function product()
    {
        return $this->belongsTo('app\model\product\Product', 'product_id', 'product_id');
    }
}
