<?php

declare (strict_types = 1);

namespace app\adminapi\middleware;

use app\service\api\admin\authority\AccessTokenService;
use app\service\api\admin\authority\AdminUserService;
use think\Exception;
use think\facade\Request;

/**
 * JWT验证刷新token机制
 */
class JWT
{
    /**
     * 登录中间件
     * @param $request
     * @param \Closure $next
     * @return object|mixed
     * @throws Exception
     */
    public function handle($request, \Closure $next): object
    {
        if (!in_array(
            Request::pathinfo(),
            [
                // 排除无需登录项
                'login/signin',
                'login/send_mobile_code',
                'common/verification/captcha',
                'common/verification/check',
            ]
        )) {
            app(AdminUserService::class)->setLogin(1);
        }

        return $next($request);
    }
}
