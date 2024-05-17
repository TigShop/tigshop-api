<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 装修模块
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\decorate;

use app\common\exceptions\ApiException;
use app\model\decorate\DecorateDiscrete;
use app\service\BaseService;

/**
 * 装修模块服务类
 */
class DecorateDiscreteService extends BaseService
{

    public function __construct()
    {
    }
    /**
     * 获取详情
     *
     * @param string $decorate_sn
     * @return DecorateDiscrete
     * @throws ApiException
     */
    public function getDetail(string $decorate_sn): DecorateDiscrete
    {
        $result = DecorateDiscrete::where('decorate_sn', $decorate_sn)->find();
        if (!$result) {
            throw new ApiException(/** LANG */'装修模块不存在');
        }
        return $result;
    }

    /**
     * 执行装修模块更新
     *
     * @param string $decorate_sn
     * @param array $data
     * @return int|bool
     * @throws ApiException
     */
    public function updateDecorateDiscrete(string $decorate_sn, array $data)
    {
        if (!$decorate_sn) {
            throw new ApiException(/** LANG */'#decorate_sn错误');
        }
        $result = DecorateDiscrete::where('decorate_sn', $decorate_sn)->save($data);
        return $result !== false;
    }
}
