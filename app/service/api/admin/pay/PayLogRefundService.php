<?php

namespace app\service\api\admin\pay;

use app\common\utils\Time;
use app\model\payment\PayLogRefund;
use app\service\api\admin\BaseService;
use Fastknife\Utils\RandomUtils;

class PayLogRefundService extends BaseService
{
    /**
     * 生成一个不重复的退款单号
     * @return string
     */
    public function createRefundSn(): string
    {
        $refund_sn = RandomUtils::getRandomCode(16);
        $pay_log_refund_id = self::getPayLogRefundId($refund_sn);
        while ($pay_log_refund_id > 0) {
            self::createRefundSn();
        }

        return $refund_sn;
    }

    /**
     * 获取退款申请ID
     * @param string $refund_sn
     * @return int
     */
    public function getPayLogRefundId(string $refund_sn): int
    {
        if (empty($refund_sn)) return 0;
        $pay_log_refund_id = PayLogRefund::where('refund_sn', $refund_sn)->value('paylog_refund_id');

        return $pay_log_refund_id ?? 0;
    }

    /**
     * 获取退款记录
     * @param string $refund_sn
     * @return array
     */
    public function getPayLogRefundByPaySn(string $refund_sn): array
    {
        return PayLogRefund::where('refund_sn', $refund_sn)->findOrEmpty()->toArray();
    }

    /**
     * 生成退款接口日志
     * @param int $pay_log_id 支付日志记录
     * @param int $order_id 订单编号
     * @param float|int $order_refund 退款金额
     * @param string $pay_code 支付类型
     * @param string $pay_log_desc 退款备注
     * @param int $admin_id 操作ID
     * @return int
     */
    public function creatPayLogRefund(int $pay_log_id, int $order_id, float|int $order_refund, string $refund_sn, string $pay_code, string $pay_log_desc, int $admin_id): int
    {
        //添加接口日志
        $data = [
            'paylog_id' => $pay_log_id,
            'refund_sn' => $refund_sn,
            'paylog_desc' => $pay_log_desc,
            'order_id' => $order_id,
            'refund_amount' => $$order_refund,
            'add_time' => Time::now(),
            'admin_id' => $admin_id,
            'pay_code' => $pay_code,
        ];
        return PayLogRefund::insertGetId($data);
    }

    /**
     * 创建支付日志参数
     * @param array $order
     * @return array
     */
    public function creatPayLogRefundParams(array $order): array
    {
        $refund_sn = self::createRefundSn();
        $params = [
            'refund_sn' => $refund_sn,
            'pay_code' => $order['pay_code'],
            'order_id' => $order['order_id'],
            'refund_amount' => $order['refund_amount'],
            'paylog_desc' => $order['paylog_desc'],
            'paylog_id' => $order['paylog_id'],
        ];

        return $params;
    }
}