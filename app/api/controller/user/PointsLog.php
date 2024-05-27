<?php
//**---------------------------------------------------------------------+
//** 通用接口控制器文件 -- 积分明细
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\api\controller\user;

use app\api\IndexBaseController;
use app\service\api\admin\user\UserPointsLogService;
use think\App;
use think\response\Json;

class PointsLog extends IndexBaseController
{
    protected UserPointsLogService $userPointsLogService;

    /**
     * 构造函数
     * @param App $app
     * @param UserPointsLogService $userPointsLogService
     * @throws \exceptions\ApiException
     */
    public function __construct(App $app, UserPointsLogService $userPointsLogService)
    {
        parent::__construct($app);
        $this->checkLogin();
        $this->userPointsLogService = $userPointsLogService;
    }

    /**
     * 列表页面
     * @return Json
     */
    public function list(): Json
    {
        $filter = $this->request->only([
            'page/d' => 1,
            'size/d' => 15,
            'sort_field' => 'log_id',
            'sort_order' => 'desc',
            'user_id' => request()->userId,
        ], 'get');
        $filterResult = $this->userPointsLogService->getFilterResult($filter);
        $total = $this->userPointsLogService->getFilterCount($filter);
        // 获取当前积分
        $userPoints = \app\model\user\User::findOrEmpty(request()->userId)->points;

        return $this->success([
            'filter_result' => $filterResult,
            'filter' => $filter,
            'total' => $total,
            "user_points" => $userPoints,
        ]);
    }

}
