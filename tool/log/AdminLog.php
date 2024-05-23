<?php

namespace log;

use think\facade\Db;
use utils\Time;
use utils\Util;

class AdminLog
{
    /**
     * 添加管理员操作日志
     *
     * @param string $message
     * @param string $app
     */
    public static function add($message)
    {
        $data=[
            'log_time'=>Time::now(),
            'user_id'=>request()->adminUid,
            'log_info'=>stripslashes($message),
            'ip_address'=>Util::getUserIp(),
        ];
        Db::name('admin_log')->insert($data);
        return true;
    }
}
