<?php

declare (strict_types = 1);

namespace app\api;

use app\BaseController;
use exceptions\ApiException;

/**
 * 控制器基础类
 */
abstract class IndexBaseController extends BaseController
{

    public function pageInfo($title, $desc, $keywords): array
    {
    }

    /**
     * 验证是否登录
     * @return void
     * @throws ApiException
     */
    public function checkLogin(): void
    {
        if (request()->userId == 0) {
            throw new ApiException('请登录', 401);
        }
    }
}
