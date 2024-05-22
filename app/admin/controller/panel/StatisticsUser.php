<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 会员统计面板
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\admin\controller\panel;

use app\admin\AdminBaseController;
use app\service\api\admin\panel\StatisticsUserService;
use think\App;
use think\Response;

/**
 * 新增会员统计控制器
 */
class StatisticsUser extends AdminBaseController
{
    protected StatisticsUserService $statisticsUserService;

    /**
     * 构造函数
     *
     * @param App $app
     */
    public function __construct(App $app, StatisticsUserService $statisticsUserService)
    {
        parent::__construct($app);
        $this->statisticsUserService = $statisticsUserService;
        $this->checkAuthor('statisticsUserManage'); //权限检查
    }

    /**
     * 新增会员趋势统计图 / 导出
     * @return Response
     * @throws \app\common\exceptions\ApiException
     */
    public function addUserTrends(): Response
    {
        $filter = $this->request->only([
            "date_type/d" => 1,
            "start_end_time" => "",
            "is_export/d" => 0,
        ], 'get');

        $filterResult = $this->statisticsUserService->getAddUserTrends($filter);

        return $this->success([
            'filter_result' => $filterResult,
        ]);
    }

    /**
     * 会员消费排行 / 导出
     * @return Response
     */
    public function userConsumptionRanking(): Response
    {
        $filter = $this->request->only([
            "keyword" => "",
            "start_time" => "",
            "end_time" => "",
            "is_export/d" => 0,
            'page/d' => 1,
            'size/d' => 15,
            'sort_field' => 'order_amount',
            'sort_order' => 'desc',
        ], 'get');

        $filterResult = $this->statisticsUserService->getUserConsumptionRanking($filter);
        $total = $this->statisticsUserService->getFilterCount($filter);

        return $this->success([
            'filter_result' => $filterResult,
            'filter' => $filter,
            'total' => $total,
        ]);
    }

    /**
     * 用户统计面板
     * @return Response
     */
    public function userStatisticsPanel(): Response
    {
        $filter = $this->request->only([
            "is_export/d" => 0,
            "start_time" => "",
            "end_time" => "",
        ], 'get');

        $filterResult = $this->statisticsUserService->getUserStatisticsPanel($filter);

        return $this->success([
            'filter_result' => $filterResult,
        ]);
    }

}
