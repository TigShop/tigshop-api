<?php
// 全局中间件定义文件
return [
    \app\adminapi\middleware\JWT::class,
    \app\middleware\AllowCrossDomain::class
];
