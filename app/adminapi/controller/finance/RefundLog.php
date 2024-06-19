<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 退款申请
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\adminapi\controller\finance;

use app\adminapi\AdminBaseController;
use app\service\api\admin\finance\RefundApplyService;
use app\service\api\admin\finance\RefundLogService;
use think\App;
use think\Response;

/**
 * 退款申请控制器
 */
class RefundLog extends AdminBaseController
{
    protected RefundLogService $refundLogService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param RefundLogService $refundLogService
     */
    public function __construct(App $app, RefundLogService $refundLogService)
    {
        parent::__construct($app);
        $this->refundLogService = $refundLogService;
        $this->checkAuthor('refundApplyManage'); //权限检查
    }

    /**
     * 列表页面
     *
     * @return Response
     */
    public function list(): Response
    {
        $filter = $this->request->only([
            'page/d' => 1,
            'size/d' => 15,
            'sort_field' => 'log_id',
            'sort_order' => 'desc',
        ], 'get');

        $filterResult = $this->refundLogService->getFilterResult($filter);
        $total = $this->refundLogService->getFilterCount($filter);

        return $this->success([
            'filter_result' => $filterResult,
            'filter' => $filter,
            'total' => $total,
        ]);
    }


}
