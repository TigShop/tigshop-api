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

namespace app\admin\controller\finance;

use app\admin\AdminBaseController;
use app\service\api\admin\finance\RefundApplyService;
use think\App;
use think\Response;

/**
 * 退款申请控制器
 */
class RefundApply extends AdminBaseController
{
    protected RefundApplyService $refundApplyService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param RefundApplyService $refundApplyService
     */
    public function __construct(App $app, RefundApplyService $refundApplyService)
    {
        parent::__construct($app);
        $this->refundApplyService = $refundApplyService;
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
            'keyword' => '',
            'refund_status' => -1,
            'page/d' => 1,
            'size/d' => 15,
            'sort_field' => 'refund_id',
            'sort_order' => 'desc',
        ], 'get');

        $filterResult = $this->refundApplyService->getFilterResult($filter);
        $total = $this->refundApplyService->getFilterCount($filter);

        return $this->success([
            'filter_result' => $filterResult,
            'filter' => $filter,
            'total' => $total,
        ]);
    }

    /**
     * 配置型
     * @return Response
     */
    public function config(): Response
    {
        $refund_status_list = \app\model\finance\RefundApply::REFUND_STATUS_NAME;
        return $this->success([
            'refund_status_list' => $refund_status_list,
        ]);
    }

    /**
     * 详情
     *
     * @return Response
     */
    public function detail(): Response
    {
        $id = input('id/d', 0);
        $item = $this->refundApplyService->getDetail($id);
        return $this->success([
            'item' => $item,
        ]);
    }

    /**
     * 执行更新操作
     *
     * @return Response
     */
    public function audit(): Response
    {
        $data = $this->request->only([
            'refund_id/d' => 0,
            'refund_status/d' => 1,
            'refund_note' => '',
            "online_balance" => "",
            "offline_balance" => "",
            "refund_balance" => "",
        ], 'post');

        $result = $this->refundApplyService->auditRefundApply($data['refund_id'], $data);
        if ($result) {
            return $this->success(/** LANG */"退款申请更新成功");
        } else {
            return $this->error(/** LANG */'退款申请更新失败');
        }
    }

    /**
     * 确认线下转账
     * @return Response
     */
    public function offlineAudit(): Response
    {
        $data = $this->request->only([
            'refund_id/d' => 0,
        ], 'post');

        $result = $this->refundApplyService->offlineAudit($data['refund_id']);
        if ($result) {
            return $this->success(/** LANG */"更新成功");
        } else {
            return $this->error(/** LANG */'更新失败');
        }
    }
}
