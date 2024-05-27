<?php

declare (strict_types=1);

namespace app\admin\middleware;


class AdminLog
{
    public function handle($request, \Closure $next)
    {
        return $next($request);
    }
}