<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 管理员消息
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\msg;

use app\model\order\Order;
use app\model\order\OrderItem;
use think\Model;
use utils\Time;

class AdminMsg extends Model
{
    protected $pk = 'msg_id';
    protected $table = 'admin_msg';

    public function getSendTimeAttr($value): string
    {
        return Time::format($value);
    }

    public function items()
    {
        // Order模型有多个OrderItem
        return $this->hasMany(OrderItem::class, 'order_id', 'order_id');
    }

    public function order()
    {
        return $this->hasOne(Order::class, 'order_id', 'order_id');
    }

    public function getOrderAttr($value)
    {
        return $value ? $value : [];
    }
}
