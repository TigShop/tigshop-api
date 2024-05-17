<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 优惠券
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\promotion;

use app\common\utils\Time;
use app\model\user\UserCoupon;
use think\Model;

class Coupon extends Model
{
    protected $pk = 'coupon_id';
    protected $table = 'coupon';
    protected $json = ['send_range_data', 'limit_user_rank'];
    protected $jsonAssoc = true;

    // 使用范围类型
    const SEND_RANGE_ALL = 0;
    const SEND_RANGE_CATEGORY = 1;
    const SEND_RANGE_BRAND = 2;
    const SEND_RANGE_PRODUCT = 3;
    const SEND_RANGE_EXCLUDING_PRODUCT = 4;

    const SEND_RANGE_MAP = [
        self::SEND_RANGE_ALL => '全场通用',
        self::SEND_RANGE_CATEGORY => '指定分类',
        self::SEND_RANGE_BRAND => '指定品牌',
        self::SEND_RANGE_PRODUCT => '指定商品',
        self::SEND_RANGE_EXCLUDING_PRODUCT => '不包含指定商品',
    ];

    // 时间格式转换
    public function getSendStartDateAttr($value)
    {
        return Time::format($value);
    }
    public function getSendEndDateAttr($value)
    {
        return Time::format($value);
    }

    public function getUseStartDateAttr($value)
    {
        return Time::format($value);
    }

    public function getUseEndDateAttr($value)
    {
        return Time::format($value);
    }

    // 优惠券是否被当前用户领取
    public function getIsReceiveAttr($value, $data)
    {
        if(isset($data["coupon_id"])){
            $time = Time::now();
            $where = [
                ['coupon_id', '=', $data["coupon_id"]],
                ['user_id', '=', request()->userId],
                ['order_id', '=', 0],
                ['start_date', '<=', $time],
                ['end_date', '>=', $time],
            ];
            if(UserCoupon::where($where)->count()){
                return 1;
            }
        }
        return 0;
    }
}
