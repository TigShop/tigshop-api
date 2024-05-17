<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 秒杀活动商品
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\promotion;

use app\model\product\ProductSku;
use think\Model;

class SeckillItem extends Model
{
    protected $pk = 'rec_id';
    protected $table = 'seckill_item';

    // 关联商品规格
    public function productSku()
    {
        return $this->hasOne(ProductSku::class, 'sku_id', 'sku_id')->bind(["sku_data","sku_stock","sku_price","sku_sn"]);
    }
}
