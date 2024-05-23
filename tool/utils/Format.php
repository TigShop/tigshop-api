<?php

namespace utils;

class Format
{

    // 模糊手机号
    public static function dimMobile(string $mobile = '')
    {
        return $mobile ? mb_substr($mobile, 0, 3, 'utf-8') . '****' . mb_substr($mobile, -4, 4, 'utf-8') : '';
    }

}
