<?php


return [
    'secret'      => env('JWT_SECRET'),
    //Asymmetric key
    'public_key'  => env('JWT_PUBLIC_KEY'),
    'private_key' => env('JWT_PRIVATE_KEY'),
    'password'    => env('JWT_PASSWORD'),
    //AccessToken 有效期，单位为秒
    'ttl'         => env('JWT_TTL',60),
    //refreshToken 有效期，单位为分钟（在refreshToken有效期内AccessToken过期时可自动刷新，刷新时支持Redis二次验证）
    'refresh_ttl' => env('JWT_REFRESH_TTL', 20160),
    //CheckLogin hashing algorithm
    'algo'        => env('JWT_ALGO', 'HS256'),
    //token获取方式，数组靠前值优先
    'token_mode'    => ['header', 'cookie', 'param'],
    //黑名单后有效期
    'blacklist_grace_period' => env('BLACKLIST_GRACE_PERIOD', 60),
    'blacklist_storage' => thans\jwt\provider\storage\Tp6::class,
];
