<?php
declare (strict_types = 1);
// 滑块验证器
return [
    'font_file' => '', //自定义字体包路径， 不填使用默认值
    //文字点击验证码
    'click_world' => [
        'backgrounds' => [],
    ],
    //滑动验证码
    'block_puzzle' => [
        'backgrounds' => app()->getRootPath() . '/tool/tig/captcha/original/', //背景图片路径， 不填使用默认值
        'templates' =>  app()->getRootPath() . '/tool/tig/captcha/slidingBlock/', //模板图
        'offset' => 10, //容错偏移量
        'is_cache_pixel' => true, //是否开启缓存图片像素值，开启后能提升服务端响应性能（但要注意更换图片时，需要清除缓存）

        // 'is_interfere' => true, //开启干扰图
    ],
    //水印
    'watermark' => [
        'fontsize' => 12,
        'color' => '#ffffff',
        'text' => 'TigShop',
    ],
    'cache' => [
        'constructor' => [\think\facade\Cache::class, 'instance'],
    ],
];
