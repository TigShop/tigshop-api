<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 会员消息
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\user;

use think\Model;
use utils\Time;

class UserMessageLog extends Model
{
    protected $pk = 'message_log_id';
    protected $table = 'user_message_log';
    protected $createTime = "send_time";
    protected $autoWriteTimestamp = true;
    const SEND_ALL_USER = 0; //全部会员
    const SEND_ONE_USER = 1; //单个会员
    const SEND_USER_RANK = 2; //会员等级
    const SEND_SOME_USER = 3; //部分会员

    const STATUS_SEND = 0; //已发送
    const STATUS_RECALL = 1; //已撤回

    // 发送类型映射
    protected const SEND_TYPE_MAP = [
        self::SEND_ALL_USER => '全部会员', //待确认，显示为待付款
        self::SEND_ONE_USER => '单个会员', //已确认，显示为待发货
        self::SEND_USER_RANK => '会员等级', //处理中，显示为已发货
        self::SEND_SOME_USER => '部分会员',
    ];

    // 状态映射
    protected const STATUS_MAP = [
        self::STATUS_SEND => '已发送',
        self::STATUS_RECALL => '已撤回',
    ];
    // 发送类型名称
    public function getSendTypeNameAttr($value, $data): string
    {
        return self::SEND_TYPE_MAP[$data['send_user_type']] ?? '';
    }

    public function getStatusNameAttr($value, $data): string
    {
        return self::STATUS_MAP[$data['is_recall']] ?? '';
    }

    public function getSendTimeAttr($value): string
    {
        return Time::format($value);
    }

}
