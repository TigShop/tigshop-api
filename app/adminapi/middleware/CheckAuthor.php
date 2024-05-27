<?php

declare (strict_types=1);

namespace app\admin\middleware;

use app\service\api\admin\authority\AuthorityService;


class CheckAuthor
{
    public function handle($request, \Closure $next)
    {
        if (false) {
            app(AuthorityService::class)->checkAuthor($author, request()->shopId, request()->authList);
        }
        return $next($request);
    }
}