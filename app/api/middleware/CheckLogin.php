<?php

declare (strict_types=1);

namespace app\api\middleware;

use app\service\api\admin\authority\AccessTokenService;
use app\service\api\admin\user\UserService;
use exceptions\ApiException;

class CheckLogin
    /**
     * 检测是否登录
     */
{
    /**
     * 登录中间件
     * @param $request
     * @param \Closure $next
     * @return object
     * @throws ApiException
     */
    public function handle($request, \Closure $next): object
    {
        // 检查token并返回数据

        if (request()->userId == 0) {
            throw new ApiException('token数据验证失败', 401);
        }

        $response = $next($request);
        return $response;
    }
}
