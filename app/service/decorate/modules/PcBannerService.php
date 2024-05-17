<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- banner
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\decorate\modules;

use app\common\exceptions\ApiException;
use app\service\BaseService;
use app\service\user\UserService;

/**
 * 装修服务类
 */
class PcBannerService extends BaseService
{
    public function __construct()
    {
    }
    /**
     * 模块数据格式化
     *
     * @param array $module
     * @return array
     * @throws ApiException
     */
    public function formatData(array $module, array $params = null): array
    {
        $module['avatar'] = app(UserService::class)->getUserAvatar();
        return $module;
    }

}
