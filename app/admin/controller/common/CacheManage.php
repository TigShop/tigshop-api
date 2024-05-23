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
namespace app\admin\controller\common;

use app\BaseController;
use app\service\api\admin\authority\AdminUserService;
use app\service\api\admin\authority\AuthorityService;
use app\service\api\admin\setting\ConfigService;
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

        return $this->success([
            'user_info' => app(AdminUserService::class)->getDetail(request()->adminUid),
            'config' => app(ConfigService::class)->getAdminConfig(),
            'main_menu' => app(AuthorityService::class)->authorityList(0, 0, request()->authList),
        ]);
    }
}
