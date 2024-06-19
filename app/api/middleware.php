<?php
// 全局中间件定义文件
return [
    \app\api\middleware\JWT::class,
    \app\middleware\AllowCrossDomain::class
];
