<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 退换货记录
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\order;

use think\Model;

class AftersalesItem extends Model
{
    protected $pk = 'aftersales_item_id';
    protected $table = 'aftersales_item';

    public function items()
    {
        return $this->hasOne(OrderItem::class, 'item_id', 'order_item_id')->bind(['order_sn', 'product_name', 'order_id', 'pic_thumb', 'product_sn', "product_id", 'quantity', 'price']);
    }

    public function aftersales()
    {
        return $this->hasOne(Aftersales::class, 'aftersale_id', 'aftersale_id')->bind(["status"]);
    }
}
