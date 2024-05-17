<?php

namespace app\service\pay;

use app\common\exceptions\ApiException;
use app\common\utils\Config;
use app\service\BaseService;

class CertificatesService extends BaseService
{
    /**
     * Bytes Length of the AES block
     */
    public const BLOCK_SIZE = 16;

    /**
     * The `aes-256-gcm` algorithm string
     */
    public const ALGO_AES_256_GCM = 'aes-256-gcm';

    /**
     * 下载平台证书
     * @return void
     * @throws ApiException
     */
    public function getCertificates(): void
    {
        $config = Config::getConfig('payment');
        $merchant_id = $config['wechat_pay_mchid'];//商户号
        $serial_no = $config['wechat_pay_serial_no'];//API证书序列号
        $apiV3Key = $config['wechat_pay_key'];
        $sign = $this->getSign("https://api.mch.weixin.qq.com/v3/certificates", "GET", "", $this->getPrivateKey(), $merchant_id, $serial_no);
        $header[] = 'User-Agent:https://zh.wikipedia.org/wiki/User_agent';
        $header[] = 'Accept:application/json';
        $header[] = 'Authorization:WECHATPAY2-SHA256-RSA2048 ' . $sign;
        try {
            $back = $this->http_Request("https://api.mch.weixin.qq.com/v3/certificates", $header);
            $data = json_decode($back, true);
            $cert = $data['data'][0]['encrypt_certificate'];
            $platform_cert = self::decrypt($cert['ciphertext'], $apiV3Key, $cert['nonce'], $cert['associated_data']);
            file_put_contents(app()->getRootPath() . '/app/common/certs/wechat/cert.pem', $platform_cert);
        } catch (\Exception $exception) {
            throw  new ApiException($exception->getMessage());
        }
    }

    /**
     * @param string $url
     * @param string $http_method [POST GET 必读大写]
     * @param string $body [请求报文主体（必须进行json编码）]
     * @param object $mch_private_key [商户私钥]
     * @param string $merchant_id [商户号]
     * @param string $serial_no [证书编号]
     * @return string
     */
    private function getSign(string $url, string $http_method, string $body, object $mch_private_key, string $merchant_id, string $serial_no): string
    {
        $timestamp = time();//时间戳
        $nonce = $timestamp . rand(10000, 99999);//随机字符串
        $url_parts = parse_url($url);
        $canonical_url = $url_parts['path'];
        $message =
            $http_method . "\n" .
            $canonical_url . "\n" .
            $timestamp . "\n" .
            $nonce . "\n" .
            $body . "\n";
        openssl_sign($message, $raw_sign, $mch_private_key, 'sha256WithRSAEncryption');
        $sign = base64_encode($raw_sign);
        $token = sprintf('mchid="%s",nonce_str="%s",timestamp="%d",serial_no="%s",signature="%s"',
            $merchant_id, $nonce, $timestamp, $serial_no, $sign);
        return $token;
    }

    /**
     * 获取商户私钥
     * @return object|false|\OpenSSLAsymmetricKey
     */
    public function getPrivateKey(): object
    {
        $private_key_file = app()->getRootPath() . '/app/common/certs/wechat/apiclient_key.pem';
        $mch_private_key = openssl_get_privatekey(file_get_contents($private_key_file));//获取私钥

        return $mch_private_key;
    }

    /**
     * 数据请求
     * @param string $url
     * @param array $header 获取头部
     * @param string $post_data POST数据，不填写默认以GET方式请求
     * @return string
     */
    public function http_Request(string $url, array $header = array(), string $post_data = ""): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 2);
        if ($post_data != "") {
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data); //设置post提交数据
        }
        //判断当前是不是有post数据的发
        $output = curl_exec($ch);
        if ($output === FALSE) {
            $output = "curl 错误信息: " . curl_error($ch);
        }
        curl_close($ch);
        return $output;
    }

    /**
     * Detect the ext-openssl whether or nor including the `aes-256-gcm` algorithm
     *
     * @throws RuntimeException
     */
    private static function preCondition(): void
    {
        if (!in_array(static::ALGO_AES_256_GCM, openssl_get_cipher_methods())) {
            throw new RuntimeException('It looks like the ext-openssl extension missing the `aes-256-gcm` cipher method.');
        }
    }

    /**
     * 解密
     * @param string $ciphertext
     * @param string $key
     * @param string $iv
     * @param string $aad
     * @return string
     */
    public static function decrypt(string $ciphertext, string $key, string $iv = '', string $aad = ''): string
    {
        self::preCondition();

        $ciphertext = base64_decode($ciphertext);
        $authTag = substr($ciphertext, $tailLength = 0 - static::BLOCK_SIZE);
        $tagLength = strlen($authTag);

        /* Manually checking the length of the tag, because the `openssl_decrypt` was mentioned there, it's the caller's responsibility. */
        if ($tagLength > static::BLOCK_SIZE || ($tagLength < 12 && $tagLength !== 8 && $tagLength !== 4)) {
            throw new ApiException('The inputs `$ciphertext` incomplete, the bytes length must be one of 16, 15, 14, 13, 12, 8 or 4.');
        }

        $plaintext = openssl_decrypt(substr($ciphertext, 0, $tailLength), static::ALGO_AES_256_GCM, $key, OPENSSL_RAW_DATA, $iv, $authTag, $aad);

        if (false === $plaintext) {
            throw new ApiException('Decrypting the input $ciphertext failed, please checking your $key and $iv whether or nor correct.');
        }

        return $plaintext;
    }


}