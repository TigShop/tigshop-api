<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 会员优惠券
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\user;

use app\common\utils\Time;
use app\model\promotion\Coupon;
use think\Model;

class UserCoupon extends Model
{
    protected $pk = 'id';
    protected $table = 'user_coupon';
    public function coupon()
    {
        return $this->hasOne(Coupon::class, 'coupon_id', 'coupon_id')->bind([
            'coupon_name',
            'store_id',
            'min_order_amount',
            'send_range_data',
            'coupon_money',
            'coupon_type',
            'coupon_discount',
            'is_global',
            'coupon_desc'
        ]);
    }

    // 优惠券状态
    const STATUS_EXPIRING_SOON = 1;
    const STATUS_NORMAL = 2;
    const STATUS_NOT_STARTED = 3;

    const STATUS_USED = 4;
    const STATUS_EXPIRED = 5;

    const STATUS_MAP = [
        self::STATUS_EXPIRING_SOON => '即将过期',
        self::STATUS_NORMAL => '正常',
        self::STATUS_NOT_STARTED => '未开始',
        self::STATUS_USED => '已使用',
        self::STATUS_EXPIRED => '已过期',
    ];

    // 状态名称
    public function getStatusNameAttr($value, $data)
    {
        if (!empty($data["order_id"])) {
            return self::STATUS_MAP[self::STATUS_USED];
        } elseif ($data["end_date"] < Time::now()) {
            return self::STATUS_MAP[self::STATUS_EXPIRED];
        } elseif ($data["start_date"] < Time::daysAgo(3) && Time::daysAgo(3, $data["end_date"]) < Time::now()) {
            return self::STATUS_MAP[self::STATUS_EXPIRING_SOON];
        } elseif ($data["start_date"] > Time::now()) {
            return self::STATUS_MAP[self::STATUS_NOT_STARTED];
        } else {
            return self::STATUS_MAP[self::STATUS_NORMAL];
        }
    }

    // 状态值
    public function getStatusAttr($value, $data)
    {
        if (!empty($data["order_id"])) {
            return self::STATUS_USED;
        } elseif ($data["end_date"] < Time::now()) {
            return self::STATUS_EXPIRED;
        } elseif ($data["start_date"] < Time::daysAgo(3) && Time::daysAgo(3, $data["end_date"]) < Time::now()) {
            return self::STATUS_EXPIRING_SOON;
        } elseif ($data["start_date"] > Time::now()) {
            return self::STATUS_NOT_STARTED;
        } else {
            return self::STATUS_NORMAL;
        }
    }

    // 优惠券使用时间
    public function getEndDateAttr($value)
    {
        return Time::format($value);
    }

    public function getStartDateAttr($value)
    {
        return Time::format($value);
    }

    // 优惠券有效期检索
    public function scopeValidityTime($query, $value)
    {
        if (!empty($value) && Time::isTimestampFormat($value)) {
            return $query->where('start_date', '<=', $value)->where('end_date', '>=', $value);
        }
        return $query;
    }

}
