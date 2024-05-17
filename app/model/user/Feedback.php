<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 会员留言
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\user;

use app\common\utils\Time;
use app\model\order\Order;
use app\model\product\Product;
use app\model\store\Store;
use think\Model;

class Feedback extends Model
{
    protected $pk = 'id';
    protected $table = 'feedback';
    protected $json = ["feedback_pics"];
    protected $jsonAssoc = true;
    protected $createTime = "add_time";
    protected $autoWriteTimestamp = true;

    // 关联商品
    public function product()
    {
        return $this->hasOne(Product::class, 'product_id', 'product_id');
    }

    // 关联订单
    public function orderInfo()
    {
        return $this->hasOne(Order::class, 'order_id', 'order_id')->bind(["order_sn"]);
    }

    // 关联店铺
    public function store()
    {
        return $this->hasOne(Store::class, 'store_id', 'store_id');
    }

    // 关联回复
    public function reply()
    {
        return $this->hasOne(Feedback::class, 'parent_id', 'id');
    }

    // 回复状态
    const STATUS_WAIT = 0;
    const STATUS_REPLY = 1;
    const STATUS_INVALID = 2;
    const STATUS_NAME = [
        self::STATUS_WAIT => '待回复',
        self::STATUS_REPLY => '已回复',
        self::STATUS_INVALID => '无效',
    ];

    // 留言类型
    const TYPE_PROPOSE = 0;
    const TYPE_COMPLAINT = 1;
    const TYPE_PRODUCT = 2;
    const TYPE_OTHER = 3;
    const TYPE_STORE_COMPLAINT = 4;
    const TYPE_ORDER_PROBLEM = 5;

    const TYPE_NAME = [
        self::TYPE_PROPOSE => '建议',
        self::TYPE_COMPLAINT => '投诉',
        self::TYPE_PRODUCT => '商品',
        self::TYPE_OTHER => '其他',
        self::TYPE_STORE_COMPLAINT => '店铺投诉',
        self::TYPE_ORDER_PROBLEM => '订单问题',
    ];

    // 回复状态
    public function getStatusNameAttr($value, $data)
    {
        return self::STATUS_NAME[$data['status']] ?? "";
    }

    // 留言类型
    public function getTypeNameAttr($value, $data)
    {
        return self::TYPE_NAME[$data['type']] ?? "";
    }

    // 留言时间
    public function getAddTimeAttr($value)
    {
        return Time::format($value);
    }

    // 是否为订单咨询
    public function scopeIsOrder($query, $value)
    {
        if (!empty($value)) {
            if ($value == 1) {
                return $query->where("order_id", 0);
            } else {
                return $query->where("order_id", ">", 0);
            }
        }
        return $query;
    }
}
