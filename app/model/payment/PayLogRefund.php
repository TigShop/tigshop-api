<?php

namespace app\model\payment;

use app\model\BaseModel;

class PayLogRefund extends BaseModel
{
    protected $pk = 'paylog_refund_id';
    protected $table = 'paylog_refund';
}