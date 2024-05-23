<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 会员
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

class UserMessage extends Model
{
    protected $pk = 'message_id';
    protected $table = 'user_message';

    protected $createTime = "add_time";
    protected $autoWriteTimestamp = true;

    public function getAddTimeDateAttr($value, $data)
    {
        return Time::format($data['add_time'], 'Y-m-d');
    }

    public function getAddTimeHmsAttr($value, $data)
    {
        return Time::format($data['add_time'], 'H:i:s');
    }
}
