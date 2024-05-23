<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 充值申请
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

class UserRechargeOrder extends Model
{
    protected $pk = 'order_id';
    protected $table = 'user_recharge_order';
    protected $createTime = "add_time";
    protected $autoWriteTimestamp = true;

    // 关联用户
    public function user()
    {
        return $this->hasOne(User::class, 'user_id', 'user_id')->bind(["username"]);
    }

    // 支付状态
    const STATUS_WAIT = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_FAIL = 2;
    const STATUS_TYPE = [
        self::STATUS_WAIT => '待确认',
        self::STATUS_SUCCESS => '已支付',
        self::STATUS_FAIL => '无效',
    ];

    // 支付状态
    public function getStatusTypeAttr($value, $data): string
    {
        return self::STATUS_TYPE[$data["status"]] ?? '';
    }

    // 添加时间
    public function getAddTimeAttr($value)
    {
        return Time::format($value);
    }

    // 支付时间
    public function getPaidTimeAttr($value)
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

    // 支付时间检索
    public function scopePaidTime($query, $value)
    {
        if (!empty($value)) {
            $value = is_array($value) ? $value : explode(',', $value);
            list($start_date, $end_date) = $value;
            $start_date = Time::toTime($start_date);
            $end_date = Time::toTime($end_date) + 86400;
            $value = [$start_date, $end_date];
            return $query->whereTime('paid_time', 'between', $value);
        }
    }

    // 已支付检索
    public function scopePaid($query)
    {
        return $query->where("status", self::STATUS_SUCCESS);
    }

}
