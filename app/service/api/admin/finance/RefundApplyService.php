<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 退款申请
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\finance;

use app\common\exceptions\ApiException;
use app\common\utils\Time;
use app\common\utils\Util;
use app\model\finance\RefundApply;
use app\model\finance\RefundLog;
use app\model\order\Aftersales;
use app\model\order\AftersalesItem;
use app\model\payment\PayLogRefund;
use app\service\api\admin\BaseService;
use app\service\api\admin\order\OrderService;
use app\service\api\admin\pay\PayLogRefundService;
use app\service\api\admin\pay\PayLogService;
use app\service\api\admin\pay\src\AliPayService;
use app\service\api\admin\pay\src\PayPalService;
use app\service\api\admin\pay\src\WechatPayService;
use app\service\api\admin\user\UserService;
use think\facade\Db;

/**
 * 退款申请服务类
 */
class RefundApplyService extends BaseService
{

    public function __construct()
    {
    }

    /**
     * 获取筛选结果
     *
     * @param array $filter
     * @return array
     */
    public function getFilterResult(array $filter): array
    {
        $query = $this->filterQuery($filter)->with(["aftersales", "order_info"])->append(["refund_type_name", "refund_status_name"]);
        $result = $query->page($filter['page'], $filter['size'])->select();

        return $result->toArray();
    }

    /**
     * 获取筛选结果数量
     *
     * @param array $filter
     * @return int
     */
    public function getFilterCount(array $filter): int
    {
        $query = $this->filterQuery($filter);
        $count = $query->count();
        return $count;
    }

    /**
     * 筛选查询
     *
     * @param array $filter
     * @return object
     */
    public function filterQuery(array $filter): object
    {
        $query = RefundApply::query();
        // 处理筛选条件

        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->keyword($filter["keyword"]);
        }

        // 退款状态
        if (isset($filter['refund_status']) && $filter["refund_status"] != -1) {
            $query->where('refund_status', $filter["refund_status"]);
        }

        if (isset($filter['sort_field'], $filter['sort_order']) && !empty($filter['sort_field']) && !empty($filter['sort_order'])) {
            $query->order($filter['sort_field'], $filter['sort_order']);
        }
        return $query;
    }

    /**
     * 获取详情
     * @param int $id
     * @return RefundApply
     * @throws ApiException
     */
    public function getDetail(int $id): RefundApply
    {
        $result = RefundApply::with(["aftersales", 'order_info'])->append(["refund_type_name", "refund_status_name"])->find($id);
        if (!$result) {
            throw new ApiException(/** LANG */'退款申请不存在');
        }
        $price = 0;
        foreach ($result->items as $item) {
            // 售后申请数量
            $item->number = AftersalesItem::where(["order_item_id" => $item->item_id, "aftersale_id" => $result->aftersale_id])->value("number") ?? 0;
            // 退款商品总价格
            $price += $item->price * $item->number;
        }

        // 排除退款成功的支付金额
        $complete_order = RefundApply::where(["order_id" => $result->order_id, "refund_status" => 1]);
        $complete_balance = $complete_order->sum('refund_balance');
        $complete_online_balance = $complete_order->sum('online_balance');
        $complete_offline_balance = $complete_order->sum('offline_balance');
        // 已完成的总金额
        $total_complete_amount = $complete_balance + $complete_online_balance + $complete_offline_balance;

        // 先判断是否有协商金额 -- 可退款的最大金额
        if ($result->refund_amount > 0) {
            $result->effective_online_balance = ($result->refund_amount - $complete_online_balance > 0) ? $result->refund_amount - $complete_online_balance : 0;
            $result->effective_balance = ($result->refund_amount - $complete_balance > 0) ? $result->refund_amount - $complete_balance : 0;
        } else {
            $result->effective_online_balance = ($result->online_paid_amount - $complete_online_balance > 0) ? $result->online_paid_amount - $complete_online_balance : 0;
            // 余额 = (退款商品总价格 / 订单商品总价格 * 订单已支付金额) - 已成功退款余额
            $effective_balance = ($price / $result->product_amount * $result->paid_amount) - $complete_balance;
            $result->effective_balance = $effective_balance > 0 ? $effective_balance : 0;
        }

        // 转换数据类型
        $result->effective_balance = Util::number_format_convert($result->effective_balance);
        $result->effective_online_balance = Util::number_format_convert($result->effective_online_balance);
        $result->total_complete_amount = Util::number_format_convert($total_complete_amount);

        return $result;
    }

    /**
     * 执行退款申请更新
     *
     * @param int $id
     * @param array $data
     * @return bool
     * @throws ApiException
     */
    public function auditRefundApply(int $id, array $data): bool
    {
        $apply = $this->getDetail($id);
        if (!$apply) {
            throw new ApiException(/** LANG */'该申请不存在');
        }

        if ($apply->refund_status != RefundApply::REFUND_STATUS_WAIT) {
            throw new ApiException(/** LANG */'申请状态值错误');
        }

        if ($data["online_balance"] > $apply->effective_online_balance) {
            throw new ApiException(/** LANG */'填写的线上金额不能超过可退的在线支付金额');
        }

        if ($data["refund_balance"] > $apply->effective_balance) {
            throw new ApiException(/** LANG */'填写的余额不能超过可退的余额');
        }

        if ($data["refund_status"] == 1 && array_sum([$data["online_balance"], $data["offline_balance"], $data["refund_balance"]]) == 0) {
            throw new ApiException(/** LANG */'退款总金额不能为0');
        }

        if ($apply->refund_amount > 0) {
            if (array_sum([$data["online_balance"], $data["offline_balance"], $data["refund_balance"]]) > ($apply->refund_amount - $apply->total_complete_amount)) {
                throw new ApiException(/** LANG */'退款总金额不能超过售后可退款金额');
            }
        } else {
            if (array_sum([$data["online_balance"], $data["offline_balance"], $data["refund_balance"]]) > ($apply->paid_amount - $apply->total_complete_amount)) {
                throw new ApiException(/** LANG */'退款总金额不能超过订单可退款金额');
            }
        }

        try {
            Db::startTrans();
            if ($data["refund_status"] == 1) {
                if ($data["online_balance"] > 0) {
                    // 执行退款流程
                    $pay_params = [
                        "order_id" => $apply->order_id,
                        'refund_id' => $apply->refund_id,
                        "order_refund" => $data["online_balance"],
                        "paylog_desc" => $data["refund_note"],
                    ];
                    RefundLog::create([
                        [
                            "order_id" => $apply->order_id,
                            "refund_apply_id" => $apply->refund_id,
                            "refund_type" => 1,
                            "refund_amount" => $data["online_balance"],
                            "user_id" => $apply->user_id,
                        ],
                    ]);
                    if ($this->refundFlow($pay_params)) {
                        $data["is_online"] = 1;
                    }
                }
                // 余额退款
                if ($data["refund_balance"] > 0) {
                    RefundLog::create([
                        [
                            "order_id" => $apply->order_id,
                            "refund_apply_id" => $apply->refund_id,
                            "refund_type" => 2,
                            "refund_amount" => $data["refund_balance"],
                            "user_id" => $apply->user_id,
                        ],
                    ]);
                    if (app(UserService::class)->incBalance($data["refund_balance"], $apply->user_id)) {
                        $data["is_receive"] = 2;
                    }
                }
                if ($data["offline_balance"] > 0) {
                    RefundLog::create([
                        [
                            "order_id" => $apply->order_id,
                            "refund_apply_id" => $apply->refund_id,
                            "refund_type" => 3,
                            "refund_amount" => $data["offline_balance"],
                            "user_id" => $apply->user_id,
                        ],
                    ]);
                    $data["is_offline"] = 1;
                }
            }
            $result = RefundApply::where('refund_id', $id)->save($data);

            Db::commit();
        } catch (\Exception $exception) {
            Db::rollback();
            throw new ApiException($exception->getMessage());
        }
        return $result;
    }

    /**
     * 线下确认已退款
     * @param $refund_id
     * @return bool
     * @throws ApiException
     */
    public function offlineAudit($refund_id): bool
    {
        $apply = $this->getDetail($refund_id);
        if (!$apply) {
            throw new ApiException(/** LANG */'退款信息不存在');
        }
        if (!$apply->canAuditOffline()) {
            throw new ApiException(/** LANG */'该状态下不能确认线下已退款');
        }
        $apply->setOfflineSuccess();
        if ($apply->checkRefundSuccess()) {
            $apply->setRefundSuccess();
        }
        return $apply->save();
    }

    /**
     * 执行退款流程
     * @param $data
     * @return bool
     * @throws ApiException
     */
    public function refundFlow($data): bool
    {
        //获取订单信息
        $order = app(OrderService::class)->getDetail($data['order_id']);
        // 获取支付信息
        $pay_log = app(PayLogService::class)->getPayLogByPaySn($order['pay_sn']);
        if (!$pay_log) {
            throw new ApiException(/** LANG */'支付信息不存在');
        }
        $refund_sn = app(PayLogRefundService::class)->createRefundSn();
        app(PayLogRefundService::class)->creatPayLogRefund($pay_log['paylog_id'], $order['order_id'], $data['order_refund'], $refund_sn, $pay_log['pay_code'], $data['paylog_desc'], request()->adminUid);
        $pay_params = [
            "pay_sn" => $pay_log['pay_sn'],
            "refund_sn" => $refund_sn,
            "order_refund" => $data["order_refund"],
            "order_amount" => $pay_log['order_amount'],
        ];
        //创建退款接口日志
        try {
            switch ($pay_log->pay_code) {
                case 'wechat':
                    $res = app(WechatPayService::class)->refund($pay_params);
                    break;
                case 'alipay':
                    $res = app(AliPayService::class)->refund($pay_params);
                    break;
                case 'paypal':
                    $res = app(PayPalService::class)->refund($pay_params);
                    break;
                default:
                    return throw new ApiException(/** LANG */'该支付方式不存在');
            }

        } catch (\Exception $exception) {
            throw new ApiException($exception->getMessage());
        }
        if (isset($res['code']) && $res['code'] == 'SUCCESS') {
            // 退款记录
            $paylog_refund = [
                "paylog_id" => $pay_log->paylog_id,
                "paylog_desc" => "线上退款成功",
                "order_id" => $data["order_id"],
                "refund_amount" => $data["order_refund"],
                "action_user" => request()->adminUid,
            ];
            if (PaylogRefund::create($paylog_refund)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 设置线上退款到账通知到财务退款
     * @param int $paylog_refund_id
     * @return bool
     */
    public function onlineRefundSuccess(int $paylog_refund_id): bool
    {
        $refundApply = RefundApply::where('paylog_refund_id', $paylog_refund_id)->find();
        if (!$refundApply) {
            return true;
        }
        $refundApply->setOnlineSuccess();
        if ($refundApply->checkRefundSuccess()) {
            $refundApply->setRefundSuccess();
        }
        return $refundApply->save();
    }

    /**
     * 获取退款金额统计
     * @param array $data
     * @return mixed
     */
    public function getRefundTotal(array $data): mixed
    {
        return RefundApply::hasWhere("orderInfo", function ($query) {
            $query->storePlatform();
        })
            ->field("SUM(online_balance + offline_balance + refund_balance) AS refund_amount")
            ->refundOrderStatus()
            ->addTime($data)
            ->findOrEmpty()->refund_amount ?? 0;
    }

    /**
     * 获取退款金额list
     * @param array $data
     * @return array
     */
    public function getRefundList(array $data): array
    {
        return RefundApply::hasWhere("orderInfo", function ($query) {
            $query->storePlatform();
        })
            ->field("SUM(RefundApply.online_balance + RefundApply.offline_balance + RefundApply.refund_balance) AS refund_amount,RefundApply.add_time")
            ->refundOrderStatus()
            ->addTime($data)
            ->select()->toArray();
    }

    /**
     * 获取退款件数统计
     * @param array $data
     * @return int
     */
    public function getRefundItemTotal(array $data): int
    {
        list($star, $end) = $data;
        $rows = Aftersales::join("aftersales_item ai", "ai.aftersale_id = aftersales.aftersale_id", "LEFT")
            ->join("order o", "o.order_id = aftersales.order_id", "LEFT")
            ->where("aftersales.status", Aftersales::STATUS_COMPLETE)
            ->whereBetween("aftersales.add_time", [Time::toTime($star), Time::toTime($end)])
            ->field("SUM(ai.number) as total");
        if (request()->storeId > 0) {
            $rows->where("o.store_id", request()->storeId);
        }
        $count = $rows->find()->total ?? 0;
        $count = intval($count);
        return $count;
    }

    /**
     * 申请退款
     * @param array $data
     * @return bool
     */
    public function applyRefund(array $data): bool
    {
        $apply_data = [
            "refund_type" => $data["refund_type"],
            "order_id" => $data["order_id"],
            "user_id" => $data["user_id"],
            "aftersale_id" => $data["aftersale_id"],
        ];
        $result = RefundApply::create($apply_data);
        return $result !== false;
    }
}
