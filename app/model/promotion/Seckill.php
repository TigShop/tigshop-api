<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 秒杀活动
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\promotion;

use app\model\product\Product;
use think\Model;
use utils\Time;

class Seckill extends Model
{
    protected $pk = 'seckill_id';
    protected $table = 'seckill';

    // 关联秒杀商品项
    public function seckillItem(): object
    {
        return $this->hasMany(SeckillItem::class, 'seckill_id', 'seckill_id')->with('product_sku');
    }

    // 关联商品
    public function product(): object
    {
        return $this->hasOne(Product::class, 'product_id', 'product_id')->bind(["product_name", "pic_thumb"]);
    }

    // 秒杀时间
    public function getSeckillStartTimeAttr($value)
    {
        return Time::format($value);
    }

    public function getSeckillEndTimeAttr($value)
    {
        return Time::format($value);
    }

    // 显示当前状态
    const STATUS_NOT_STARTED = 0;
    const STATUS_STARTED = 1;
    const STATUS_ENDED = 2;
    const STATUS_NAME = [
        self::STATUS_NOT_STARTED => '未开始',
        self::STATUS_STARTED => '进行中',
        self::STATUS_ENDED => '已结束',
    ];

    public function getStatusNameAttr($value, $data)
    {
        if (!empty($data['seckill_start_time']) || !empty($data['seckill_end_time'])) {
            if (Time::now() < $data['seckill_start_time']) {
                return self::STATUS_NAME[self::STATUS_NOT_STARTED];
            } elseif (Time::now() > $data['seckill_end_time']) {
                return self::STATUS_NAME[self::STATUS_ENDED];
            } else {
                return self::STATUS_NAME[self::STATUS_STARTED];
            }
        }
        return "--";
    }
}
