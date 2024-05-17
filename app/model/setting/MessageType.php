<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 消息设置
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\setting;

use think\Model;

class MessageType extends Model
{
    protected $pk = 'message_id';
    protected $table = 'message_type';
    protected $createTime = "add_time";
    protected $autoWriteTimestamp = true;

    public function templateMessage()
    {
        return $this->hasMany(MessageTemplate::class, 'message_id', 'message_id')->append(["type_name"]);
    }
}
