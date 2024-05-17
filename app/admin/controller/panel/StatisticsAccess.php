<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 访问统计
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\admin\controller\panel;

use app\admin\AdminBaseController;
use app\service\panel\StatisticsAccessService;
use think\App;
use think\Response;

/**
 * 新增会员统计控制器
 */
class StatisticsAccess extends AdminBaseController
{
    protected StatisticsAccessService $statisticsAccessService;

    /**
     * 构造函数
     *
     * @param App $app
     */
    public function __construct(App $app, StatisticsAccessService $statisticsAccessService)
    {
        parent::__construct($app);
        $this->statisticsAccessService = $statisticsAccessService;
        $this->checkAuthor('statisticsAccessManage'); //权限检查
    }

    /**
     * 访问统计
     * @return Response
     * @throws \app\common\exceptions\ApiException
     */
    public function accessStatistics(): Response
    {
        $filter = $this->request->only([
            "is_hits/d" => 1,
            "start_time" => "",
            "end_time" => "",
        ], 'get');

        $filterResult = $this->statisticsAccessService->getAccessStatistics($filter);

        return $this->success([
            'filter_result' => $filterResult,
        ]);
    }
}
