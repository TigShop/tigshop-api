<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 退款申请
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\finance;

use think\Model;

class RefundLog extends Model
{
    protected $pk = 'log_id';
    protected $table = 'refund_log';
    protected $createTime = "add_time";
    protected $autoWriteTimestamp = 'int';

    protected $append = ['refund_type_text'];

    protected const REFUND_TYPE_MAP = [
        1 => '原路返回',
        2 => '余额退回',
        3 => '线下退回'
    ];

    public function getRefundTypeTextAttr($value, $data): string
    {
        return self::REFUND_TYPE_MAP[$data['refund_type']] ?? '';
    }

    public function refund(): \think\model\relation\HasOne
    {
        return $this->hasOne('app\model\finance\RefundApply', 'refund_id', 'refund_apply_id');
    }
}
