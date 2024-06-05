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

class Shop extends Model
{
    protected $pk = 'shop_id';
    protected $table = 'shop';

    protected $createTime = 'add_time';
    protected $autoWriteTimestamp = 'int';
    protected $append = [
        'status_text',
    ];
    // 字段处理
    public function getAddTimeAttr($value): string
    {
        return Time::format($value);
    }

    const STATUS_LIST = [
        1 => '开业',
        10 => '关店'
    ];


    public function getStatusTextAttr($value, $data): string
    {
        return self::STATUS_LIST[$data['status']] ?: '';
    }

    public function merchant()
    {
        return $this->hasOne(Merchant::class, 'merchant_id', 'merchant_id');
    }
}
