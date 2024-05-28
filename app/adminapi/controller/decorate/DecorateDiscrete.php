<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 装修模块
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\adminapi\controller\decorate;

use app\adminapi\AdminBaseController;
use app\service\api\admin\decorate\DecorateDiscreteService;
use think\App;
use think\Response;

/**
 * 装修模块控制器
 */
class DecorateDiscrete extends AdminBaseController
{
    protected DecorateDiscreteService $decorateDiscreteService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param DecorateDiscreteService $decorateDiscreteService
     */
    public function __construct(App $app, DecorateDiscreteService $decorateDiscreteService)
    {
        parent::__construct($app);
        $this->decorateDiscreteService = $decorateDiscreteService;
        $this->checkAuthor('decorateDiscreteManage'); //权限检查
    }

    /**
     * 详情
     * @return Response
     */
    public function detail(): Response
    {
        $decorate_sn = input('decorate_sn', '');
        $item = $this->decorateDiscreteService->getDetail($decorate_sn);
        return $this->success([
            'item' => $item,
        ]);
    }

    /**
     * 执行更新操作
     *
     * @return Response
     */
    public function update(): Response
    {
        $decorate_sn = input('decorate_sn', '');
        $data = $this->request->only([
            'decorate_sn' => $decorate_sn,
            'data' => [],
        ], 'post');

        $result = $this->decorateDiscreteService->updateDecorateDiscrete($decorate_sn, $data);
        if ($result) {
            return $this->success(/** LANG */'装修模块更新成功');
        } else {
            return $this->error(/** LANG */'装修模块更新失败');
        }
    }

}
