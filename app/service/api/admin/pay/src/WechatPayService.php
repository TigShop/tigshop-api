<?php

namespace app\service\api\admin\pay\src;

use app\service\api\admin\oauth\UserAuthorizeService;
use app\service\api\admin\pay\PaymentService;
use app\service\api\admin\pay\PayService;
use common\Url;
use EasyWeChat\Pay\Application;
use exceptions\ApiException;
use think\Exception;
use utils\Config;
use utils\Util;

class WechatPayService extends PayService
{
    const JSAPI_PAY = 'wechat'; //jsapi支付
    const APP_PAY = 'app'; //app支付
    const NATIVE_PAY = 'pc'; //扫码支付
    const MINI_PROGRAM_PAY = 'miniProgram'; //小程序支付
    const HTML_PAY = 'h5'; //h5支付
    const KEY_LENGTH_BYTE = 32;
    const AUTH_TAG_LENGTH_BYTE = 16;

    private $payType = 'null';

    protected string $appId = '';

    public function __construct()
    {
        $appid = Config::get('wechat_appId');
        if ($this->payType == self::MINI_PROGRAM_PAY) {
            $appid = Config::get('wechat_miniProgram_appId');
        }
        if ($this->payType == self::APP_PAY) {
            $appid = Config::get('mini_appid');
        }
        $this->appId = $appid;
    }

    public function setPayType(string $type): WechatPayService
    {
        $this->payType = $type;
        return $this;
    }

    public function getPayType(): string
    {
        if ($this->payType === null) {
            return Util::getClientType();
        } else {
            return $this->payType;
        }
    }

    /**
     * 微信支付公共请求
     * @param array $order
     * @return array
     * @throws ApiException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function pay(array $order): array
    {
        if (empty($order['pay_sn']) || empty($order['user_id']) || empty($order['order_amount'])) {
            throw new ApiException('缺少支付参数！');
        }
        try {
            switch ($this->getPayType()) {
                case self::JSAPI_PAY:

                    return $this->JsApiPay($order);
                case self::NATIVE_PAY:

                    return $this->NativePay($order);
                case self::MINI_PROGRAM_PAY:

                    return $this->MiniPay($order);
                case self::APP_PAY:

                    return $this->AppPay($order);
                case self::HTML_PAY:

                    return $this->HtmlPay($order);
                default:
                    throw new ApiException('#无效支付类型');
            }
        } catch (Exception $exception) {
            throw new ApiException($exception->getMessage());

        }
    }

    /**
     * 退款
     * @param array $order ['pay_sn' => '平台支付订单号','order_refund' => '退款金额','refund_sn' => '退款单号','order_amount' => '订单总金额']
     * @return array|string[]
     * @throws ApiException
     */
    public function refund(array $order): array
    {
        try {
            $response = $this->getApplication()->getClient()->postJson('/v3/refund/domestic/refunds', $this->getRefundData($order['pay_sn'], $order['refund_sn'], $order['order_refund'], $order['order_amount']));
            $res = $response->toArray(false);
            if (isset($res['status']) && ($res['status'] == 'SUCCESS' || $res['status'] == 'PROCESSING')) {
                return ['code' => 'SUCCESS', 'message' => '支付成功'];
            } else {
                return ['code' => 'FAIL', 'message' => $res['message']];
            }
        } catch (\Exception $exception) {
            throw new ApiException($exception->getMessage());
        }
    }

    /**
     * 回调处理
     * @return string[]
     * @throws ApiException
     */
    public function notify(): array
    {
        $message = request()->post();
        if ($message['event_type'] === 'TRANSACTION.SUCCESS') {
            $resource = $message['resource'];
            //1.解密参数
            $data = $this->decryptToString($resource['associated_data'], $resource['nonce'], $resource['ciphertext']);
            $data = json_decode($data, true);
            //查询订单
            $query_data = $this->queryOrderPay($data['out_trade_no']);
            if ($query_data['trade_state'] == 'trade_state') {
                //支付成功--设置订单未已支付
                $pay_sn = $query_data['out_trade_no'];
                app(PaymentService::class)->paySuccess($pay_sn);
                return ['code' => 'SUCCESS', 'message' => '支付成功'];
            }
            return ['code' => 'FAIL', 'message' => '失败'];
        } else {
            return ['code' => 'FAIL', 'message' => '失败'];
        }
    }

    /**
     * 回调处理
     * @return array
     * @throws ApiException
     */
    public function refund_notify(): array
    {
        $message = request()->post();
        if ($message['event_type'] === 'REFUND.SUCCESS') {
            $resource = $message['resource'];
            $data = $this->decryptToString($resource['associated_data'], $resource['nonce'], $resource['ciphertext']);
            $data = json_decode($data, true);
            if (isset($data['out_refund_no'])) {
                $refund_sn = $data['out_refund_no'];
                app(PaymentService::class)->refundSuccess($refund_sn);
                return ['code' => 'SUCCESS', 'message' => '支付成功'];
            }
            return ['code' => 'FAIL', 'message' => '失败'];
        } else {
            return ['code' => 'FAIL', 'message' => '失败'];
        }
    }

    /**
     * JSAPI支付
     * @param array $order
     * @return array
     * @throws ApiException
     */
    public function JsApiPay(array $order): array
    {
        try {
            $openid = app(UserAuthorizeService::class)->getUserAuthorizeOpenId($order['user_id'], 1);
            if (empty($openid)) {
                throw new ApiException('openid不能为空！');
            }
            $response = $this->getApplication()->getClient()->postJson('/v3/pay/transactions/jsapi', $this->getPayData($order['pay_sn'], $order['order_amount'], '', $openid));
            $res = $response->toArray(false);
            if (!isset($res['prepay_id'])) {
                throw new ApiException($res['message']);
            }
            $utils = $this->getApplication()->getUtils();

            return $utils->buildBridgeConfig($res['prepay_id'], $this->appId, 'RSA');
        } catch (\Exception $exception) {
            throw new ApiException($exception->getMessage());
        }
    }

    /**
     * 扫码支付
     * @param array $order
     * @return array
     * @throws ApiException
     */
    public function NativePay(array $order): array
    {
        try {
            $response = $this->getApplication()->getClient()->postJson('/v3/pay/transactions/native', $this->getPayData($order['pay_sn'], $order['order_amount']));
            return $response->toArray(true);
        } catch (\Exception $exception) {
            throw new ApiException($exception->getMessage());
        }
    }

    /**
     * @param array $order
     * @return array
     * @throws ApiException
     */
    public function AppPay(array $order): array
    {
        try {
            $response = $this->getApplication()->getClient()->postJson('/v3/pay/transactions/app', $this->getPayData($order['pay_sn'], $order['order_amount']));
            $res = $response->toArray(false);
            $utils = $this->getApplication()->getUtils();
            return $utils->buildAppConfig($res['prepay_id'], $this->appId);
        } catch (\Exception $exception) {
            throw new ApiException($exception->getMessage());
        }
    }

    /**
     * 小程序支付
     * @param array $order
     * @return array
     * @throws ApiException
     */
    public function MiniPay(array $order): array
    {
        try {
            $openid = app(UserAuthorizeService::class)->getUserAuthorizeOpenId($order['user_id'], 2);
            if (empty($openid)) {
                throw new ApiException('openid不能为空！');
            }
            $response = $this->getApplication()->getClient()->postJson('/v3/pay/transactions/jsapi', $this->getPayData($order['pay_sn'], $order['order_amount'], '', $openid));
            $res = $response->toArray(false);
            if (!isset($res['prepay_id'])) {
                throw new ApiException($res['message']);
            }
            $utils = $this->getApplication()->getUtils();
            return $utils->buildMiniAppConfig($res['prepay_id'], $this->appId, 'RSA');
        } catch (\Exception $exception) {
            throw new ApiException($exception->getMessage());
        }
    }

    /**
     * h5支付
     * @param array $order
     * @return array
     * @throws ApiException
     */
    public function HtmlPay(array $order): array
    {
        try {
            $res = $this->getApplication()->getClient()->postJson('/v3/pay/transactions/h5', $this->getPayData($order['pay_sn'], $order['order_amount'], ''));
            if (!isset($res['h5_url'])) {
                throw new ApiException('支付唤起失败！');
            }
            return ['code_url' => $res['h5_url']];
        } catch (\Exception $exception) {
            throw new ApiException($exception->getMessage());

        }
    }

    /**
     * 拼接支付请求参数
     * @param string $out_trade_no
     * @param float $total_fee
     * @param string $notify_url
     * @param string $openid
     * @return array|array[]
     */
    public function getPayData(string $out_trade_no, float $total_fee, string $notify_url = '', string $openid = ''): array
    {
        $total_fee = intval($total_fee * 100);
        //查询是否是服务商模式
        $config = Config::getConfig('payment');
        $sp_appid = isset($config['wechat_pay_sp_mchid']) ? $config['wechat_pay_sp_mchid'] : '';
        $sp_mchid = $config['wechat_pay_mchid'];
        if (!empty($sp_appid) && !empty($sp_mchid)) {
            //服务商模式
            $data = [
                'sp_appid' => $sp_appid,
                'sp_mchid' => $sp_mchid,
                'sub_mchid' => $config['wechat_pay_mchid'],
                'out_trade_no' => $out_trade_no,
                'appid' => $this->appId,
                'description' => '商品购买',
                'notify_url' => $notify_url ?: $this->getNotifyUrl(),
                'amount' => [
                    'total' => $total_fee,
                    'currency' => 'CNY',
                ],
            ];
            if (!empty($openid)) {
                $data['json']['payer'] = ['sub_openid' => $openid];
            }
        } else {
            $data = [
                'mchid' => $config['wechat_pay_mchid'],
                'out_trade_no' => $out_trade_no,
                'appid' => $this->appId,
                'description' => '商品购买',
                'notify_url' => $notify_url ?: $this->getNotifyUrl(),
                'amount' => [
                    'total' => $total_fee,
                    'currency' => 'CNY',
                ],
            ];
            if (!empty($openid)) {
                $data['payer'] = ['openid' => $openid];
            }
        }
        return $data;
    }

    /**
     * 获取退款参数
     * @param string $out_trade_no
     * @param float $refund
     * @param float $total
     * @return array
     */
    public function getRefundData(string $out_trade_no, string $refund_sn, float $refund = 0, float $total = 0): array
    {
        $refund = intval($refund * 100);
        $total = intval($total * 100);
        $data = [
            'out_trade_no' => $out_trade_no,
            'out_refund_no' => $refund_sn, //做日志处理,需校验是否重复
            'amount' => [
                'refund' => $refund,
                'total' => $total,
                'currency' => 'CNY',
            ],
            'notify_url' => $this->getRefundNotifyUrl()
        ];

        return $data;
    }

    /**
     * 获取支付回调地址
     * @return string
     */
    public function getNotifyUrl(): string
    {
        return 'https://demo2.lyecs.com/index/test';
        return Url::app('');
    }

    /**
     * 获取退款通知地址
     * @return string
     */
    public function getRefundNotifyUrl(): string
    {
        return 'https://demo2.lyecs.com/index/test';
        return Url::app('');
    }

    /**
     * 查询订单结果
     * @param string $outTradeNo
     * @return array
     * @throws ApiException
     */
    public function queryOrderPay(string $outTradeNo): array
    {
        $cfg = Config::getConfig('payment');
        try {
            $response = $this->getApplication()->getClient()->get("v3/pay/transactions/out-trade-no/" . $outTradeNo, [
                'query' => [
                    'mchid' => $cfg['wechat_pay_mchid'],
                ],
            ]);
            return $response->toArray();

        } catch (\Exception $exception) {
            throw new ApiException($exception->getMessage());
        }

    }

    /**
     * 公共请求
     * @return object|Application
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     */
    public function getApplication(): object
    {
        //平台证书序列号
        $cfg = Config::getConfig('payment');
        $config = [
            'mch_id' => $cfg['wechat_pay_mchid'],
            // 商户证书
            'private_key' => app()->getRootPath() . '/runtime/certs/wechat/apiclient_key.pem',
            'certificate' => app()->getRootPath() . '/runtime/certs/wechat/apiclient_cert.pem',
            // v3 API 秘钥
            'secret_key' => $cfg['wechat_pay_key'],
            'platform_certs' => [
                app()->getRootPath() . '/runtime/certs/wechat/cert.pem',
            ],
            'http' => [
                'throw' => true, // 状态码非 200、300 时是否抛出异常，默认为开启
                'timeout' => 5.0,
            ],
        ];

        return new Application($config);
    }

    /**
     * 微信通知解密
     * @param $associatedData
     * @param $nonceStr
     * @param $ciphertext
     * @return false|string
     * @throws \SodiumException
     */
    public function decryptToString($associatedData, $nonceStr, $ciphertext)
    {
        $cfg = Config::getConfig('payment');
        $aesKey = $cfg['wechat_pay_key'];
        $ciphertext = \base64_decode($ciphertext);
        if (strlen($ciphertext) <= self::AUTH_TAG_LENGTH_BYTE) {
            return false;
        }
        // ext-sodium (default installed on >= PHP 7.2)
        if (function_exists('\sodium_crypto_aead_aes256gcm_is_available') && \sodium_crypto_aead_aes256gcm_is_available()) {
            return \sodium_crypto_aead_aes256gcm_decrypt($ciphertext, $associatedData, $nonceStr, $aesKey);
        }
        // ext-libsodium (need install libsodium-php 1.x via pecl)
        if (function_exists('\Sodium\crypto_aead_aes256gcm_is_available') && \Sodium\crypto_aead_aes256gcm_is_available()) {
            return \Sodium\crypto_aead_aes256gcm_decrypt($ciphertext, $associatedData, $nonceStr, $aesKey);
        }
        // openssl (PHP >= 7.1 support AEAD)
        if (PHP_VERSION_ID >= 70100 && in_array('aes-256-gcm', \openssl_get_cipher_methods())) {
            $ctext = substr($ciphertext, 0, -self::AUTH_TAG_LENGTH_BYTE);
            $authTag = substr($ciphertext, -self::AUTH_TAG_LENGTH_BYTE);
            return \openssl_decrypt($ctext, 'aes-256-gcm', $aesKey, \OPENSSL_RAW_DATA, $nonceStr,
                $authTag, $associatedData);
        }
        throw new \RuntimeException('AEAD_AES_256_GCM需要PHP 7.1以上或者安装libsodium-php');
    }
}
