<?php

declare (strict_types=1);

namespace app\adminapi\middleware;


class AdminLog
{
    public function handle($request, \Closure $next)
    {
        return $next($request);
    }
}