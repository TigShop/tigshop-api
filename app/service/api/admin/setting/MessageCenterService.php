<?php

namespace app\service\api\admin\setting;

use app\job\MessageJob;
use app\job\MiniProgramJob;
use app\job\SmsJob;
use app\job\WechatJob;
use app\service\api\admin\oauth\UserAuthorizeService;
use app\service\api\admin\order\OrderService;
use app\service\core\BaseService;
use utils\Config;
use utils\TigQueue;

class MessageCenterService extends BaseService
{

    const NEW_ORDER = 1; //会员下单
    const ORDER_PAY = 2; //下单支付
    const ORDER_SHIPPING = 3;//订单发货
    const ORDER_REFUND = 4;//订单退款
    const NEW_ORDER_SHOP = 5;//下单给商家发送信息
    const ORDER_PAY_SHOP = 6;//支付订单给商家发送信息
    const  ORDER_INVOICE = 7;//发票邮寄
    protected array $messageTypeList = [
        self::NEW_ORDER,
        self::ORDER_PAY,
        self::ORDER_SHIPPING,
        self::ORDER_REFUND,
        self::NEW_ORDER_SHOP,
        self::ORDER_PAY_SHOP,
        self::ORDER_INVOICE,
    ];

    /**
     * 发送消息
     * @param int $user_id
     * @param int $order_id
     * @param int $type
     * @return bool|array
     * @throws \exceptions\ApiException
     */
    public function sendUserMessage(int $user_id, int $order_id, int $type): bool|array
    {
        if (!in_array($type, $this->messageTypeList)) return false;
        $order = app(OrderService::class)->getOrder($order_id, $user_id);
        $template_info = app(MessageTemplateService::class)->getMessageTemplateList($type);
        if ($template_info['type_info']['is_message'] == 1 && $template_info['message']['disabled'] == 0) {
            //需要发送站内信
            $title = $template_info['message']['title'];
            $content = $template_info['message']['content'];
            $link = [
                'path' => 'order',
                'label' => '订单提醒',
                'id' => $order_id,
                'name' => $title
            ];
            //替换content内容
            $content = str_replace('{order_sn}', $order->order_sn, $content);
            if ($type == self::ORDER_SHIPPING) {
                $content = str_replace('{logistics_name}', $order->logistics_name, $content);
                $content = str_replace('{tracking_no}', $order->tracking_no, $content);
            }
            app(TigQueue::class)->push(MessageJob::class,
                ['user_id' => $user_id, 'title' => $title, 'content' => $content, 'link' => $link]);
            return true;
        }
        if ($template_info['type_info']['is_msg'] == 1 && $template_info['msg']['disabled'] == 0) {
            //需要发送短信
            $template_code = $template_info['msg']['template_id'];
            $content = [];
            $mobile = $order->mobile;
            if (in_array($type, [self::NEW_ORDER, self::ORDER_PAY, self::ORDER_REFUND, self::ORDER_INVOICE, self::ORDER_PAY_SHOP, self::NEW_ORDER_SHOP])) {
                $content['order'] = $order->order_sn;
            }
            if ($type == self::ORDER_SHIPPING) {
                $content['order'] = $order->order_sn;
                $content['shipping'] = $order->logistics_name;
                $content['code'] = $order->tracking_no;
            }
            if (in_array($type, [self::ORDER_PAY_SHOP, self::NEW_ORDER_SHOP])) {
                $mobile = Config::get('sms_shop_mobile');
            }
            app(TigQueue::class)->push(SmsJob::class,
                ['mobile' => $mobile, 'template_code' => $template_code, 'content' => $content]);
            return true;
        }
        if ($template_info['type_info']['is_wechat'] == 1 && $template_info['wechat']['disabled'] == 0) {
            //需要发送公众号消息
            $openid = app(UserAuthorizeService::class)->getUserAuthorizeOpenId($user_id, 1);
            if (empty($openid)) return false;
            $template_id = $template_info['wechat']['template_id'];
            if (empty($template_id)) return false;
            $h5_domain = Config::get('h5_domain');
            if (empty($h5_domain)) {
                $h5_domain = Config::get('pc_domain');
            }
            $url = $h5_domain . '/pages/user/order/info?id=' . $order_id;;
            $message = [
                'touser' => $openid,
                'template_id' => $template_id,
                'url' => $url,
            ];
            $data = [];
            if ($type == self::ORDER_PAY) {
                $data = [
                    'character_string3' => ['value' => $order->order_sn],
                    'time7' => ['value' => $order->add_time],
                    'amount4' => ['value' => $order->total_amount],
                ];
            }
            if ($type == self::ORDER_SHIPPING) {
                $data = [
                    'thing21' => ['value' => $order->logistics_name],
                    'character_string18' => ['value' => $order->tracking_no],
                    'time3' => ['value' => $order->shipping_time],
                    'thing17' => ['value' => $order->consignee . ' ' . $order->mobile],
                    'character_string2' => ['value' => $order->order_sn],
                ];
            }
            if ($type == self::ORDER_REFUND) {
                $data = [
                    'character_string5' => ['value' => $order->order_sn],
                    'amount2' => ['value' => $order->refund_money],
                ];
            }
            $message['data'] = $data;
            app(TigQueue::class)->push(WechatJob::class, $message);
            return true;
        }
        if ($template_info['type_info']['is_mini_program'] == 1 && $template_info['mini_program']['disabled'] == 0) {
            //需要小程序消息
            $openid = app(UserAuthorizeService::class)->getUserAuthorizeOpenId($user_id, 2);
            if (empty($openid)) return false;
            $template_id = $template_info['mini_program']['template_id'];
            if (empty($template_id)) return false;
            $page = '/pages/user/order/info?id=' . $order_id;
            $message = [
                'touser' => $openid,
                'template_id' => $template_id,
                'page' => $page,
            ];
            $data = [];
            if ($type == self::ORDER_PAY) {
                $data = [
                    'character_string2' => ['value' => $order->order_sn],
                    'time1' => ['value' => $order->add_time],
                    'amount4' => ['value' => $order->total_amount],
                ];
            }
            if ($type == self::ORDER_SHIPPING) {
                $data = [
                    'thing4' => ['value' => $order->logistics_name],
                    'character_string5' => ['value' => $order->tracking_no],
                    'date3' => ['value' => $order->shipping_time],
                    'thing8' => ['value' => $order->consignee . ' ' . $order->mobile],
                    'character_string2' => ['value' => $order->order_sn],
                ];
            }
            $message['data'] = $data;
            app(TigQueue::class)->push(MiniProgramJob::class, $message);
            return true;
        }

        return true;
    }
}