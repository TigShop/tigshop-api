<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 退款申请
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\finance;

use app\model\order\Aftersales;
use app\model\order\Order;
use think\Model;
use utils\Time;

class RefundApply extends Model
{
    protected $pk = 'refund_id';
    protected $table = 'refund_apply';
    protected $createTime = "add_time";
    protected $autoWriteTimestamp = true;

    // 关联订单
    public function orderInfo(): \think\model\relation\HasOne
    {
        return $this->hasOne(Order::class, 'order_id', 'order_id')
            ->bind(['product_amount', 'coupon_amount', 'total_amount', 'paid_amount', 'unpaid_amount', "points_amount",
                "discount_amount", "balance", 'online_paid_amount', 'offline_paid_amount', "order_sn", "items"]);
    }

    public function log()
    {
        return $this->hasMany(RefundLog::class, 'refund_id', 'refund_apply_id');
    }

    // 关联售后
    public function aftersales(): \think\model\relation\HasOne
    {
        return $this->hasOne(Aftersales::class, 'aftersale_id', 'aftersale_id')->bind(["aftersales_sn", "refund_amount"]);
    }

    /**
     * 是否能确认线下打款
     * @return bool
     */
    public function canAuditOffline(): bool
    {
        return $this->is_offline == 1;
    }

    /**
     * 设置线下已转账完成
     * @return void
     */
    public function setOfflineSuccess()
    {
        $this->is_offline = 2;
    }

    /**
     * 设置线上转账成功
     * @return void
     */
    public function setOnlineSuccess()
    {
        $this->is_online = 2;
    }

    /**
     * 确认已全部退款
     * @return bool
     */
    public function checkRefundSuccess()
    {
        return $this->is_offline != 1 && $this->is_online != 1 && $this->is_receive != 1;
    }

    /**
     * 设置全部退款已处理
     * @return void
     */
    public function setRefundSuccess()
    {
        $this->refund_status = self::REFUND_STATUS_PROCESSED;
    }

    // 退款类型
    const REFUND_TYPE_ORDER = 1;
    const REFUND_TYPE_PRODUCT = 2;
    const REFUND_TYPE_NAME = [
        self::REFUND_TYPE_ORDER => '订单',
        self::REFUND_TYPE_PRODUCT => '商品',
    ];

    // 退款状态
    const REFUND_STATUS_WAIT = 0;
    const REFUND_STATUS_PROCESSING = 1;
    const REFUND_STATUS_PROCESSED = 2;
    const REFUND_STATUS_CANCEL = 3;
    const REFUND_STATUS_NAME = [
        self::REFUND_STATUS_WAIT => '待处理',
        self::REFUND_STATUS_PROCESSING => '处理中',
        self::REFUND_STATUS_PROCESSED => '已处理',
        self::REFUND_STATUS_CANCEL => '已取消',
    ];

    public function getRefundStatusNameAttr($value, $data): string
    {
        return self::REFUND_STATUS_NAME[$data['refund_status']] ?? '';
    }

    public function getRefundTypeNameAttr($value, $data): string
    {
        return self::REFUND_TYPE_NAME[$data['refund_type']] ?? '';
    }

    // 查询已退款订单
    public function scopeRefundOrderStatus($query)
    {
        return $query->where('refund_status', self::REFUND_STATUS_PROCESSED);
    }

    // 下单时间检索
    public function scopeAddTime($query, $value)
    {
        if (!empty($value) && is_array($value)) {
            list($start_date, $end_date) = $value;
            $start_date = Time::toTime($start_date);
            $end_date = Time::toTime($end_date) + 86400;
            $value = [$start_date, $end_date];
            return $query->whereTime('refund_apply.add_time', 'between', $value);
        }
        return $query;
    }

    // 订单号 + 售后单号
    public function scopeKeyword($query, $value)
    {
        if (!empty($value)) {
            return $query->join('order', 'refund_apply.order_id = order.order_id')
                ->join('aftersales', 'refund_apply.aftersale_id = aftersales.aftersale_id')
                ->field('refund_apply.*')
                ->where(function ($query) use ($value) {
                    $query->where('order.order_sn', 'like', "%$value%")
                        ->whereOr('aftersales.aftersales_sn', 'like', "%$value%");
                });
        }
        return $query;
    }

}
