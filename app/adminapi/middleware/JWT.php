<?php

declare (strict_types = 1);

namespace app\adminapi\middleware;

use app\constant\ResponseCode;
use app\service\api\admin\authority\AccessTokenService;
use app\service\api\admin\authority\AdminUserService;
use exceptions\ApiException;
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
            $result = app(AccessTokenService::class)->setApp('admin')->checkToken();
            if ($result) {
                // 获取adminUid
                $admin_id = intval($result['data']->adminId);
                if (!$admin_id) {
                    throw new Exception('token数据验证失败', ResponseCode::NOT_TOKEN);
                }
                app(AdminUserService::class)->setLogin($admin_id);
                if (request()->adminType == 'shop' && Request::pathinfo() != 'merchant/shop/my_shop' && !in_array(request()->shopId,
                        request()->shopIds)) {
                    throw new ApiException('非法请求');
                }
            } else {
                // token验证失败
                throw new Exception('token验证失败', ResponseCode::NOT_TOKEN);
            }
        }

        return $next($request);
    }
}
