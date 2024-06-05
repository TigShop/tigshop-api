<?php

namespace app\service\api\admin\pay;

use app\model\payment\PayLog;
use app\model\payment\PayLogRefund;
use app\service\api\admin\finance\RefundApplyService;
use app\service\api\admin\finance\UserRechargeOrderService;
use app\service\api\admin\order\OrderDetailService;
use app\service\api\admin\order\OrderService;
use app\service\core\BaseService;
use exceptions\ApiException;
use utils\Config;
use utils\Time;

class PaymentService extends BaseService
{
    /**
     * 获取支付配置
     * @return array
     */
    public function getConfig(): array
    {
        $config = Config::getConfig('payment');
        return $config;
    }

    /**
     * 获取支付配置
     * @return array
     */
    public function getAvailablePayment(string $type = 'order'): array
    {
        $payment = [];
        $config = $this->getConfig();
        if (!empty($config['use_wechat']) && $config['use_wechat'] == 1) {
            $payment[] = 'wechat';
        }
        if (!empty($config['use_alipay']) && $config['use_alipay'] == 1) {
            $payment[] = 'alipay';
        }
        if (!empty($config['paypal']) && $config['paypal'] == 1) {
            $payment[] = 'paypal';
        }
        if (!empty($config['use_offline']) && $config['use_offline'] == 1 && $type == 'order') {
            $payment[] = 'offline';
        }
        return $payment;
    }

    /**
     * 支付回调成功后处理
     * @param int $pay_id
     * @return void
     * @throws ApiException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function paySuccess(string $pay_sn): void
    {
        $pay_log = app(PayLogService::class)->getPayLogByPaySn($pay_sn);
        if (!$pay_log || $pay_log['pay_status'] == 1) {
            return;
        }
        if (empty($pay_log['order_id'])) return;
        try {
            //修改支付状态
            app(PayLog::class)->where('paylog_id', $pay_log['paylog_id'])->save(['pay_status' => 1]);
            switch ($pay_log['order_type']) {
                case 0:
                    //更新订单中的支付单号
                    $order = app(OrderService::class)->getOrder($pay_log['order_id']);
                    $order->out_trade_no = $pay_sn;
                    $order->save();
                    app(OrderDetailService::class)->setOrderId($pay_log['order_id'])->setPaidMoney($pay_log['pay_amount'])->updateOrderMoney();
                    break;
                case 1:
                    //充值
                    app(UserRechargeOrderService::class)->setRechargePaid($pay_log['order_id']);
                    break;
                default:
                    break;
            }
        } catch (\Exception $exception) {
            throw new ApiException($exception->getMessage());
        }
    }

    /**
     * 退款回调成功处理
     * @param string $refund_sn
     * @return void
     * @throws ApiException
     */
    public function refundSuccess(string $refund_sn): void
    {
        $pay_log_refund = app(PayLogRefundService::class)->getPayLogRefundByPaySn($refund_sn);
        if (!$pay_log_refund || $pay_log_refund['status'] == 1) {
            return;
        }
        if (empty($pay_log['order_id'])) return;
        try {
            //修改通知状态
            app(PayLogRefund::class)->where('paylog_refund_id', $pay_log_refund['paylog_refund_id'])->save(['status' => 1, 'notify_time' => Time::now()]);
            app(RefundApplyService::class)->onlineRefundSuccess($pay_log_refund['paylog_refund_id']);
        } catch (\Exception $exception) {
            throw new ApiException($exception->getMessage());
        }
    }

}
