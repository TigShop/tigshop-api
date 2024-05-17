<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 短信发送日志
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\msg;

use think\Model;

class SmsLog extends Model
{
    protected $pk = 'sms_id';
    protected $table = 'sms_log';
    protected $createTime = "send_time";
    protected $autoWriteTimestamp = true;

}
