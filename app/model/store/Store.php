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

namespace app\model\store;

use app\common\utils\Time;
use app\model\user\User;
use think\Model;

class Store extends Model
{
    protected $pk = 'store_id';
    protected $table = 'store';

    protected $createTime = 'add_time';
    protected $autoWriteTimestamp = 'int';

    // 字段处理
    public function getAddTimeAttr($value): string
    {
        return Time::format($value);
    }

    // 用户名
    public function userName()
    {
        return $this->hasOne(User::class, "user_id", "user_id")->bind(['user_name']);
    }
}
