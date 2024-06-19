<?php

namespace app\job;

use app\model\user\UserMessage;
use utils\Time;

class MessageJob extends BaseJob
{
    /**
     * 发布站内信
     * @param $data [user_id => '会员id',title => '标题',content => '内容',link => '链接地址']
     * @return bool
     */
    public function doJob($data): bool
    {
        try {
            $insert = [
                'user_id' => $data['user_id'],
                'title' => $data['title'],
                'content' => $data['content'],
                'link' => json_encode($data['link']),
                'add_time' => Time::now()
            ];
            UserMessage::create($data);
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }
}