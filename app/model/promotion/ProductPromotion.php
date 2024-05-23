<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 优惠活动
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\promotion;

use think\Model;
use utils\Time;

class ProductPromotion extends Model
{
    protected $pk = 'promotion_id';
    protected $table = 'product_promotion';
    protected $json = ['limit_user_rank', 'range_data', 'promotion_type_data'];
    protected $jsonAssoc = true;

    // 优惠活动类型
    const PROMOTION_TYPE_FULL_REDUCE = 1;
    const PROMOTION_TYPE_FULL_DISCOUNT = 2;
    const PROMOTION_TYPE_FULL_REDUCE_NAME = 3;

    // 优惠活动类型映射
    protected const PROMOTION_TYPE_MAP = [
        self::PROMOTION_TYPE_FULL_REDUCE => '满减',
        self::PROMOTION_TYPE_FULL_DISCOUNT => '折扣',
        self::PROMOTION_TYPE_FULL_REDUCE_NAME => '赠品',
    ];

    // 优惠活动状态
    const PROMOTION_STATUS_ON = 1;
    const PROMOTION_STATUS_OFF = 2;
    const PROMOTION_STATUS_FORTHCOMING = 3;
    const PROMOTION_STATUS_NAME = [
        self::PROMOTION_STATUS_ON => '活动进行中',
        self::PROMOTION_STATUS_OFF => '活动已结束',
        self::PROMOTION_STATUS_FORTHCOMING => '活动未开始',
    ];

    // 优惠活动类型名称
    public function getPromotionTypeNameAttr($value, $data): string
    {
        return self::PROMOTION_TYPE_MAP[$data['promotion_type']] ?? '';
    }

    // 结束时间
    public function getEndTimeAttr($value)
    {
        return Time::format($value);
    }

    //优惠活动开始时间
    public function getStartTimeAttr($value)
    {
        return Time::format($value);
    }

    // 活动状态
    public function getProductStatusAttr()
    {
        $end_time = $this->end_time;
        $start_time = $this->start_time;
        if (!empty($end_time) && !empty($start_time)) {
            if (time() < strtotime($start_time)) {
                $status = 3;
            } elseif (time() > strtotime($end_time)) {
                $status = 2;
            } else {
                $status = 1;
            }
            return self::PROMOTION_STATUS_NAME[$status] ?? '';
        }
        return "";
    }

    // 活动时间
    public function getProductTimeAttr()
    {
        $end_time = $this->end_time;
        $start_time = $this->start_time;
        if (!empty($end_time) && !empty($start_time)) {
            return [$start_time, $end_time];
        }
        return [];
    }

    // 活动状态检索
    public function scopeProductStatus($query, $status)
    {
        switch ($status) {
            case self::PROMOTION_STATUS_ON:
                $query->where('start_time', '<=', time())->where('end_time', '>=', time());
                break;
            case self::PROMOTION_STATUS_OFF:
                $query->where('end_time', '<', time());
                break;
            case self::PROMOTION_STATUS_FORTHCOMING:
                $query->where('start_time', '>', time());
                break;
        }
    }
}
