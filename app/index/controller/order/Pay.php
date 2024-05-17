<?php

namespace app\index\controller\order;

use app\common\exceptions\ApiException;
use app\common\utils\Config;
use app\index\IndexBaseController;
use app\service\order\OrderDetailService;
use app\service\order\OrderService;
use app\service\pay\PayLogService;
use app\service\pay\PaymentService;
use app\service\pay\src\AliPayService;
use app\service\pay\src\PayPalService;
use app\service\pay\src\WechatPayService;
use think\App;
use think\response\Json;

class Pay extends IndexBaseController
{
    /**
     * 构造函数
     *
     * @param App $app
     */
    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    /**
     * 订单支付
     * @return \think\Response
     * @throws \app\common\exceptions\ApiException
     */
    public function index(): \think\Response
    {
        $order_id = input('id/d', 0);
        $orderDetail = app(OrderDetailService::class)->setId($order_id)->setUserId(request()->userId);
        // 检查订单是否可支付
        $orderDetail->checkActionAvailable('to_pay');

        $order = $orderDetail->getOrder()->toArray();
        $payment_list = app(PaymentService::class)->getAvailablePayment();
        if ($order['pay_type_id'] == 1) {
            $payment_list = array_diff($payment_list, ['offline']);
        } elseif ($order['pay_type_id'] == 2) {

        } elseif ($order['pay_type_id'] == 3) {
            $payment_list = array_diff($payment_list, ['wechat', 'alipay', 'paypal']);
        }
        $payment_list = array_values($payment_list);
        $offline_payment_list = [];
        if (in_array('offline', $payment_list)) {
            $offline_payment_list = [
                'offline_pay_bank' => str_replace('{$order_sn}', $order['order_sn'], Config::get('offline_pay_bank', 'payment')),
                'offline_pay_company' => str_replace('{$order_sn}', $order['order_sn'], Config::get('offline_pay_company', 'payment')),
            ];
        }
        return $this->success([
            'order' => $order,
            'payment_list' => $payment_list,
            'offline_payment_list' => $offline_payment_list,
        ]);
    }

    /**
     * 检测订单支付状态
     * @return \think\Response
     */
    public function checkStatus(): \think\Response
    {
        $order_id = input('id/d', 0);
        $pay_log_id = input('paylog_id/d', 0);
        if (empty($order_id) && empty($pay_log_id)) {
            return $this->error('参数缺失');
        }

        if (!empty($order_id)) {
            $pay_status = app(OrderService::class)->getPayStatus($order_id);
        } else {
            $pay_status = app(PayLogService::class)->getPayStatus($pay_log_id);
        }
        //根据后台配置项来确定使用那些支付类型
        return $this->success([
            'pay_status' => $pay_status,
        ]);
    }

    /**
     * 订单支付
     * @return \think\Response
     * @throws ApiException
     */
    public function create(): \think\Response
    {
        $order_id = input('id/d', 0);
        $pay_type = input('type', '');
        if (empty($pay_type)) {
            return $this->error('未选择支付方式');
        }

        $orderDetail = app(OrderDetailService::class)->setId($order_id)->setUserId(request()->userId);
        $orderDetail->checkActionAvailable('to_pay');
        $order = $orderDetail->getOrder()->toArray();
        $order['order_type'] = 0;
        $order['pay_code'] = $pay_type;
        $pay_params = app(PayLogService::class)->creatPayLogParams($order);
        $pay_params['paylog_id'] = app(PayLogService::class)->creatPayLog($pay_params);
        $pay_params['user_id'] = request()->userId;
        try {
            switch ($pay_type) {
                case 'wechat':
                    $res = app(WechatPayService::class)->pay($pay_params);
                    break;
                case 'alipay':
                    $res = app(AliPayService::class)->pay($pay_params);
                    break;
                case 'paypal':
                    $res = app(PayPalService::class)->pay($pay_params);
                    break;
                default:
                    return $this->error('未选择支付方式');
            }
        } catch (\Exception $exception) {
            throw new ApiException($exception->getMessage());
        }
        if (isset($res['code']) && $res['code'] == '1001') {

            return $this->error($res['msg']);
        }

        return $this->success([
            'order_id' => $pay_params['order_id'],
            'order_sn' => $pay_params['order_sn'],
            'order_amount' => $pay_params['order_amount'],
            'pay_info' => $res,
        ]);
    }

    /**
     * @return bool
     * @throws ApiException
     * @throws \Throwable
     */
    public function notify(): string
    {
        $pay_type = input('pay_code', '');
        try {
            switch ($pay_type) {
                case 'wechat':
                    $res = app(WechatPayService::class)->notify();
                    break;
                case 'alipay':
                    $res = app(AliPayService::class)->notify();
                    break;
                case 'paypal':
                    $res = app(PayPalService::class)->notify();
                    break;
                default:
                    $res = app(WechatPayService::class)->notify();
            }
        } catch (\Exception $exception) {
            return json_encode(['code' => 'FAIL', 'message' => '失败']);
        }

        return json_encode($res);
    }

    /**
     * 退款回调地址
     * @return string
     */
    public function refundNotify(): string
    {
        $pay_type = input('pay_code', '');
        try {
            switch ($pay_type) {
                case 'wechat':
                    $res = app(WechatPayService::class)->refund_notify();
                    break;
                case 'alipay':
                    $res = app(AliPayService::class)->refund_notify();
                    break;
                case 'paypal':
                    $res = app(PayPalService::class)->refund_notify();
                    break;
                default:
                    $res = app(WechatPayService::class)->refund_notify();
            }
        } catch (\Exception $exception) {
            return json_encode(['code' => 'FAIL', 'message' => '失败']);
        }

        return json_encode($res);
    }

}
