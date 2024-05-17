<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 授权
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\user;

use think\Model;

class UserAuthorize extends Model
{
    protected $pk = 'authorize_id';
    protected $table = 'user_authorize';
    protected $createTime = "add_time";
    protected $autoWriteTimestamp = true;

    // 授权类型
    const AUTHORIZE_TYPE_WECHAT = 1;
    const AUTHORIZE_TYPE_ALIPAY = 2;
    const AUTHORIZE_TYPE_QQ = 3;
    const AUTHORIZE_TYPE_MINI_PROGRAM = 11;

    const AUTHORIZE_TYPE_NAME = [
        self::AUTHORIZE_TYPE_WECHAT => '微信',
        self::AUTHORIZE_TYPE_ALIPAY => '支付宝',
        self::AUTHORIZE_TYPE_QQ => 'QQ',
        self::AUTHORIZE_TYPE_MINI_PROGRAM => '小程序',
    ];

    public function getAuthorizeTypeNameAttr($value, $data)
    {
        return self::AUTHORIZE_TYPE_NAME[$data['authorize_type']] ?? "";
    }
}
