<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 退款日志
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\finance;

use think\Model;

class PaylogRefund extends Model
{
    protected $pk = 'refund_id';
    protected $table = 'paylog_refund';
    protected $createTime = "add_time";
    protected $autoWriteTimestamp = true;
}
