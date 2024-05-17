<?php

namespace app\model\payment;

use app\model\BaseModel;
use app\model\order\Order;

class PayLog extends BaseModel
{
    protected $pk = 'paylog_id';
    protected $table = 'paylog';
    protected $createTime = "add_time";
    protected $autoWriteTimestamp = true;
    protected $json = ["notify_data"];
    protected $jsonAssoc = true;

    public function orderInfo()
    {
        return $this->hasOne(Order::class, 'order_id', 'order_id')->bind(["consignee"]);
    }

    // 支付状态
    const PAY_STATUS_UNPAID = 0;
    const PAY_STATUS_PAID = 1;
    const PAY_STATUS_FAIL = 2;
    const PAY_STATUS_NAME = [
        self::PAY_STATUS_UNPAID => '待支付',
        self::PAY_STATUS_PAID => '已支付',
        self::PAY_STATUS_FAIL => '支付失败',
    ];

    public function getPayStatusNameAttr($value, $data)
    {
        return self::PAY_STATUS_NAME[$data['pay_status']] ?? '';
    }
}