<?php

declare (strict_types=1);

namespace app\admin\middleware;

use app\service\authority\AccessTokenService;
use app\service\authority\AdminUserService;
use app\service\authority\AuthorityService;
use think\Exception;
use think\facade\Request;


class CheckAuthor
{
    public function handle($request, \Closure $next)
    {
        if (false) {
            app(AuthorityService::class)->checkAuthor($author,request()->storeId,request()->authList);
        }
        return $next($request);
    }
}