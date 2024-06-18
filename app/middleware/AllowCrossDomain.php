<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2023 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace app\middleware;

use Closure;
use think\Request;
use think\Response;

/**
 * 跨域请求支持
 */
class AllowCrossDomain
{

    protected $header = [
        'Access-Control-Allow-Credentials' => 'true',
        'Access-Control-Max-Age' => 1800,
        'Access-Control-Allow-Methods' => 'GET, POST, PATCH, PUT, DELETE, OPTIONS',
        'Access-Control-Allow-Headers' => 'Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-CSRF-TOKEN, X-Requested-With,x-client-type,authorization,X-Shop-Id',
    ];


    /**
     * 允许跨域请求
     * @access public
     * @param Request $request
     * @param Closure $next
     * @param array $header
     * @return Response
     */
    public function handle(Request $request, Closure $next, array $header = []): Response
    {
        $header = !empty($header) ? array_merge($this->header, $header) : $this->header;
        $allowList = config('app.allow_cross_domain');
        if (!isset($header['Access-Control-Allow-Origin'])) {
            $origin = $request->header('origin');

            if ($origin && ('' == $allowList || in_array($origin, $allowList))) {
                $header['Access-Control-Allow-Origin'] = $origin;
            } else {
                $header['Access-Control-Allow-Origin'] = '*';
            }
        }
        if ($request->method() == 'OPTIONS') {
            header("HTTP/1.1 200 OK");
            exit();
        }

        return $next($request)->header($header);
    }
}
