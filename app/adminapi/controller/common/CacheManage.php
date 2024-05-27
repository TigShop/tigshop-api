<?php
//**---------------------------------------------------------------------+
//**   后台控制器文件 -- 缓存管理
//**---------------------------------------------------------------------+
//**   版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//**   作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//**   提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+
namespace app\adminapi\controller\common;

use app\BaseController;
use tig\CacheManager;

class CacheManage extends BaseController
{

    public function __construct()
    {
    }

    public function cleanup()
    {
        $tag = input('tag', 'all');
        app(CacheManager::class)->clearCacheByTag($tag);
        return $this->success('缓存清除成功');
    }
}
