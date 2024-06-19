<?php

namespace app\service\api\admin\pay\src;

use app\service\api\admin\pay\PayService;
use app\service\api\admin\setting\RegionService;
use common\Url;
use utils\Config;

class PayPalService extends PayService
{
    public function pay(array $order): array
    {
        $data_amount = $order['order_amount'];
        $data_return_url = urlencode($this->getReturnUrl($order['order_sn']));
        $data_pay_account = Config::get('paypal_account');
        $currency_code = Config::get('paypal_currency');
        $data_notify_url = $this->getNotifyUrl($order['order_sn']);
        $cancel_return = Url::app();
        $province = app(RegionService::class)->getName($order['province']);
        $city = app(RegionService::class)->getName($order['city']);;
        $consignee = explode(' ', $order['consignee']);
        if (!isset($consignee[1])) {
            $consignee[1] = '';
        }
        $order['address'] = str_replace('/', '\\', $order['address']);
        $link = 'https://www.paypal.com/cgi-bin/webscr?' .   // 不能省略
            "cmd=_xclick" .                             // 不能省略
            "&business=$data_pay_account" .                 // 贝宝帐号
            "&item_name=$order[order_sn]" .                 // payment for
            "&amount=$data_amount" .                        // 订单金额
            "&currency_code=$currency_code" .            // 货币
            "&return=$data_return_url" .                    // 付款后页面
            "&invoice=$order[order_sn]" .                      // 订单号
            "&charset=utf-8" .                              // 字符集
            "&no_shipping=0" .                              // 不要求客户提供收货地址
            "&no_note=" .                                  // 付款说明
            "&country_code=AU" .
            "&country=AU" .
            "&notify_url=$data_notify_url" .
            "&first_name=" . $consignee[0] .
            "&last_name=" . $consignee[1] .
            "&state=" . urlencode($province) .
            "&city=" . urlencode($city) .
            "&address1=" . urlencode($order['address']) .
            "&email=" . $order['email'] .
            "&zip=" . $order['zipcode'] .
            "&phone=" . $order['mobile'] .
            "&cancel_return=$cancel_return";

        return ['url' => $link];
    }

    public function refund(array $order): array
    {
        return [];
    }

    public function notify(): array
    {
        return [];
    }

    public function refund_notify(): array
    {
        return [];
    }

}