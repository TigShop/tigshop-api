<?php

declare(strict_types=1);

namespace app\adminapi;

use app\BaseController;
use exceptions\ApiException;

/**
 * 控制器基础类
 */
abstract class AdminBaseController extends BaseController
{
    /**
     * 权限验证
     *
     * @param string $author
     * @return bool
     */
    public function checkAuthor($author = ''): bool
    {
        return true;
//         return app(AuthorityService::class)->checkAuthor($author,request()->shopId,request()->authList);
    }

    /**
     * 判断
     * @param int $shopId
     * @return false
     */
    public function checkShopAuth(int $shopId, bool $throwException = true): bool
    {
        if ($shopId != request()->ShopId) {
            if (
                $throwException
            ) {
                new ApiException('数据不存在');
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * 判断
     * @param int $merchant_id
     * @return false
     */
    public function checkMerchantAuth(int $merchant_id, bool $throwException = true): bool
    {
        if ($merchant_id != request()->merchantId) {
            if (
                $throwException
            ) {
                new ApiException('数据不存在');
            } else {
                return false;
            }
        }
        return true;
    }
}
