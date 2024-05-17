<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 模板消息表
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\setting;

use think\Model;

class MessageTemplate extends Model
{
    protected $pk = 'id';
    protected $table = 'message_template';

    //定义消息类型
    const MESSAGE_TYPE_WECHAT = 1;
    const MESSAGE_TYPE_MIN_PROGRAM = 2;
    const MESSAGE_TYPE_MSG = 3;
    const MESSAGE_TYPE_MESSAGE = 4;
    const MESSAGE_TYPE_APP = 5;
    const MESSAGE_TYPE_DING = 6;

    const MESSSAGE_TYPE = [
        self::MESSAGE_TYPE_WECHAT => '微信模板消息',
        self::MESSAGE_TYPE_MIN_PROGRAM => '小程序订阅消息',
        self::MESSAGE_TYPE_MSG => '短信',
        self::MESSAGE_TYPE_MESSAGE => '站内信',
        self::MESSAGE_TYPE_APP => 'APP',
        self::MESSAGE_TYPE_DING => '钉钉',
    ];

    // 获取消息类型
    public function getTypeNameAttr($value, $data): string
    {
        return self::MESSSAGE_TYPE[$data['type']] ?? '';
    }

}
