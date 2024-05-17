<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 装修分类商品（宽）模块
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
use app\service\decorate\MobileCatNavService;

/**
 * 装修服务类
 */
class CatNavService extends BaseService
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
    public function formatData(array $module): array
    {

        $filterResult = app(MobileCatNavService::class)->getFilterResult([
            'is_show' => 1,
        ]);
        $module['nav_list'] = $filterResult;
        return $module;
    }

}
