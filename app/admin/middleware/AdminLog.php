<?php

declare (strict_types=1);

namespace app\admin\middleware;

use app\service\authority\AccessTokenService;
use app\service\authority\AdminUserService;
use think\Exception;
use think\facade\Request;


class AdminLog
{
    public function handle($request, \Closure $next)
    {
        return $next($request);
    }
}