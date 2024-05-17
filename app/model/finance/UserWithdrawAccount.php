<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 支付账号信息
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\finance;

use think\Model;

class UserWithdrawAccount extends Model
{
    protected $pk = 'account_id';
    protected $table = 'user_withdraw_account';

    //账号类型
    const ACCOUNT_TYPE_BANK = 1;
    const ACCOUNT_TYPE_ALIPAY = 2;
    const ACCOUNT_TYPE_WECHAT = 3;

    const ACCOUNT_TYPE_NAME = [
        self::ACCOUNT_TYPE_BANK => '银行卡',
        self::ACCOUNT_TYPE_ALIPAY => '支付宝',
        self::ACCOUNT_TYPE_WECHAT => '微信',
    ];

    // 账号类型名称
    public function getAccountTypeNameAttr($value, $data): string
    {
        return self::ACCOUNT_TYPE_NAME[$data["account_type"]] ?? '';
    }
}
