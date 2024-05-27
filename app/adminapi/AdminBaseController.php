<?php

declare(strict_types=1);

namespace app\adminapi;

use app\BaseController;

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
}
