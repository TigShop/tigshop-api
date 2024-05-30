<?php

declare (strict_types=1);

namespace app\adminapi\middleware;

use app\service\api\admin\authority\AccessTokenService;
use app\service\api\admin\authority\AdminUserService;
use exceptions\ApiException;
use think\facade\Request;

/**
 * JWT验证刷新token机制
 */
class MerchantJWT
{
    /**
     * 登录中间件
     * @param $request
     * @param \Closure $next
     * @return object|mixed
     */
    public function handle($request, \Closure $next): object
    {

        $result = app(AccessTokenService::class)->setApp('admin')->checkToken();
        if ($result) {
            // 获取adminUid
            $admin_id = intval($result['data']->adminId);
            if (!$admin_id) {
                throw new ApiException('token数据验证失败', 401);
            }
            app(AdminUserService::class)->setLogin($admin_id);
        } else {
            // token验证失败
            throw new ApiException('token验证失败', 401);
        }

        return $next($request);
    }
}
