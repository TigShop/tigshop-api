<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 提现申请
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\finance;

use app\model\user\User;
use think\Model;
use utils\Time;

class UserWithdrawApply extends Model
{
    protected $pk = 'id';
    protected $table = 'user_withdraw_apply';
    protected $json = ["account_data"];
    protected $jsonAssoc = true;

    protected $createTime = 'add_time';
    protected $autoWriteTimestamp = true;

    // 关联用户
    public function user()
    {
        return $this->hasOne(User::class, "user_id", "user_id")->bind(["username"]);
    }

    // 关联账号信息
    public function userWithdrawAccount()
    {
        return $this->hasMany(UserWithdrawAccount::class, "user_id", "user_id");
    }

    // 处理状态
    const STATUS_WAIT = 0;
    const STATUS_FINISHED = 1;
    const STATUS_REJECT = 2;

    const STATUS_TYPE = [
        self::STATUS_WAIT => '待处理',
        self::STATUS_FINISHED => '已完成',
        self::STATUS_REJECT => '拒绝申请',
    ];

    public function getStatusTypeAttr($value, $data): string
    {
        return self::STATUS_TYPE[$data["status"]] ?? '';
    }

    // 完成时间
    public function getFinishedTimeAttr($value): string
    {
        return Time::format($value);
    }

    // 会员名称检索
    public function scopeUsername($query, $value)
    {
        return $query->hasWhere("user", function ($query) use ($value) {
            $query->where("username", "like", "%{$value}%");
        });
    }

    // 完成时间检索
    public function scopeFinishedTime($query, $value)
    {
        if (!empty($value)) {
            list($start_date, $end_date) = $value;
            $start_date = Time::toTime($start_date);
            $end_date = Time::toTime($end_date) + 86400;
            $value = [$start_date, $end_date];
            return $query->whereTime('finished_time', 'between', $value);
        }
    }
}
