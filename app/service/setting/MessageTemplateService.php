<?php

namespace app\service\setting;

use app\model\setting\MessageTemplate;
use app\model\setting\MessageType;
use app\service\BaseService;

class MessageTemplateService extends BaseService
{
    /**
     * 获取消息模板详细信息
     * @param int $message_id
     * @return array|array[]
     */
    public function getMessageTemplateList(int $message_id = 0): array
    {
        $message_type_info = MessageType::findOrEmpty($message_id);
        if (empty($message_type_info)) return [];
        $return = [
            'message' => [],
            'wechat' => [],
            'mini_program' => [],
            'msg' => [],
            'app' => [],
        ];
        $return['type_info'] = $message_type_info;
        //站内信处理
        $message_info = MessageTemplate::where([['type', '=', 4], ['message_id', '=', $message_id]])->findOrEmpty();
        if ($message_type_info['is_message'] == -1) {
            $return['message']['disabled'] = 1;
        } else {
            $return['message']['disabled'] = 0;
        }
        $return['message']['info'] = $message_info;
        //公众号处理
        $wechat_info = MessageTemplate::where([['type', '=', 1], ['message_id', '=', $message_id]])->findOrEmpty();
        if ($message_type_info['is_wechat'] == -1) {
            $return['wechat']['disabled'] = 1;
        } else {
            $return['wechat']['disabled'] = 0;
        }
        $return['wechat']['info'] = $wechat_info;
        //小程序处理
        $mini_program_info = MessageTemplate::where([['type', '=', 2], ['message_id', '=', $message_id]])->findOrEmpty();
        if ($message_type_info['is_mini_program'] == -1) {
            $return['mini_program']['disabled'] = 1;
        } else {
            $return['mini_program']['disabled'] = 0;
        }
        $return['mini_program']['info'] = $mini_program_info;
        //短信处理
        $msg_info = MessageTemplate::where([['type', '=', 3], ['message_id', '=', $message_id]])->findOrEmpty();
        if ($message_type_info['is_msg'] == -1) {
            $return['msg']['disabled'] = 1;
        } else {
            $return['msg']['disabled'] = 0;
        }
        $return['msg']['info'] = $msg_info;
        //app处理
        $app_info = MessageTemplate::where([['type', '=', 5], ['message_id', '=', $message_id]])->findOrEmpty();
        if ($message_type_info['is_app'] == -1) {
            $return['app']['disabled'] = 1;
        } else {
            $return['app']['disabled'] = 0;
        }
        $return['app']['info'] = $app_info;

        return $return;
    }
}