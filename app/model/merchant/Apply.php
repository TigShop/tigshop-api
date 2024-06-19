<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 店铺
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\merchant;

use app\model\user\User;
use think\Model;
use utils\Time;

class Apply extends Model
{
    protected $pk = 'merchant_apply_id';
    protected $table = 'merchant_apply';

    protected $createTime = 'add_time';
    protected $autoWriteTimestamp = 'int';
    protected $json = ['merchant_data', 'base_data', 'shop_data'];
    protected $jsonAssoc = true;

    protected $append = ['status_text'];

    public final function getStatusTextAttr($value, $data): string
    {
        return isset($data['status']) && self::STATUS_LIST[$data['status']] ? self::STATUS_LIST[$data['status']] : '';
    }

    const STATUS_LIST = [
        1 => '待审核',
        10 => '审核通过',
        20 => '审核未通过'
    ];
    // 字段处理
    public function getAddTimeAttr($value): string
    {
        return Time::format($value);
    }

    // 用户名
    public function user()
    {
        return $this->hasOne(User::class, "user_id", "user_id")->field(["user_id", 'username']);
    }
}
