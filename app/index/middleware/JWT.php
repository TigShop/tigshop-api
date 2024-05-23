<?php

declare (strict_types = 1);

namespace app\index\middleware;

use app\service\api\admin\authority\AccessTokenService;
use app\service\api\admin\user\UserService;
use think\Exception;

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
     * @throws \exceptions\ApiException
     */
    public function handle($request, \Closure $next): object
    {
        request()->userId = 0;
        // 检查token并返回数据
        try {
            $result = app(AccessTokenService::class)->setApp('app')->checkToken();
            if ($result) {
                // 获取appUid
                $user_id = intval($result['data']->appId);
                if (!$user_id) {
                    throw new Exception('token数据验证失败', 401);
                }
                app(UserService::class)->setLogin($user_id);
            }
        } catch (\Exception $e) {
            // token无效或过期
        }
        // 测试
        //app(UserService::class)->setLogin(1);

        $response = $next($request);
        return $response;
    }
}
