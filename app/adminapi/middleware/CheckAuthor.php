<?php

declare (strict_types=1);

namespace app\adminapi\middleware;

use app\service\api\admin\authority\AuthorityService;


class CheckAuthor
{
    public function handle($request, \Closure $next)
    {
        $authority_sn = $request->all('authorityCheckAppendName');
        if ($authority_sn) {
            app(AuthorityService::class)->checkAuthor($authority_sn, (int)request()->shopId, request()->authList);
        }
        return $next($request);
    }
}