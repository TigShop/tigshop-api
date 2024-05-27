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
     * @throws \app\common\exceptions\ApiException
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
            // 检查token并返回数据
            $result = app(AccessTokenService::class)->setApp('adminapi')->checkToken();
            if ($result) {
                // 获取adminUid
                $admin_id = intval($result['data']->adminId);
                if (!$admin_id) {
                    throw new Exception('token数据验证失败', 401);
                }
                app(AdminUserService::class)->setLogin($admin_id);
            } else {
                // token验证失败
                throw new Exception('token验证失败', 401);
            }
        }

        return $next($request);
    }
}
