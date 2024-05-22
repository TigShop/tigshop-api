<?php
//**---------------------------------------------------------------------+
//** 通用接口控制器文件 -- 售后模块                                  +
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\index\controller\user;

use app\index\IndexBaseController;
use app\service\api\admin\order\AftersalesService;
use app\service\api\admin\order\OrderService;
use think\App;
use think\Response;

class Aftersales extends IndexBaseController
{
    protected AftersalesService $aftersalesService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param AftersalesService $aftersalesService
     */
    public function __construct(App $app, AftersalesService $aftersalesService)
    {
        parent::__construct($app);
        $this->checkLogin();
        $this->aftersalesService = $aftersalesService;
    }

    /**
     * 可售后订单列表
     * @return Response
     * @throws \think\db\exception\DbException
     */
    public function list(): Response
    {
        $filter = [
            'size' => 15,
            'sort_field' => 'order_id',
            'sort_order' => 'desc',
        ];
        $filter["page"] = input("page/d", 1);

        $result = $this->aftersalesService->afterSalesOrderList($filter);
        $total = $this->aftersalesService->afterSalesOrderFilter()->count();
        return $this->success([
            'filter_result' => $result,
            'total' => $total,
            'filter' => $filter,
        ]);
    }

    /**
     * 配置型
     * @return Response
     */
    public function config(): Response
    {
        // 售后服务类型
        $aftersale_type = \app\model\order\Aftersales::AFTERSALES_TYPE_NAME;
        $aftersale_reason =  \app\model\order\Aftersales::AFTERSALES_REASON;
        return $this->success([
            'aftersale_type' => $aftersale_type,
            'aftersale_reason' => $aftersale_reason
        ]);
    }


    /**
     * 售后申请详情
     * @return Response
     */
    public function applyData(): Response
    {
        $item_id = input('item_id/d', 0);
        $order_id = input('order_id/d', 0);
        $list = $this->aftersalesService->getAfterSalesDetail($order_id, $item_id);
        return $this->success([
            'list' => $list,
            'order' => app(OrderService::class)->getOrder($order_id),
        ]);
    }

    /**
     * 售后申请
     * @return Response
     */
    public function create(): Response
    {
        $data = $this->request->only([
            'order_id/d' => 0,
            'aftersale_type/d' => 1,
            'aftersale_reason' => '',
            "description" => "",
            'refund_amount' => 0.00,
            "pics/a" => [],
            "items" => [
                "order_item_id/d" => 0,
                'number/d' => 1,
            ],
        ], 'post');
        $result = $this->aftersalesService->afterSalesApply($data);
        return $result ? $this->success(/** LANG */"售后申请成功,请耐心等待我们的审核") : $this->error(/** LANG */'售后申请失败');
    }

    /**
     * 售后申请记录
     * @return Response
     */
    public function getRecord(): Response
    {
        $filter = $this->request->only([
            'page' => 1,
            'size' => 15,
            'sort_field' => 'aftersale_id',
            'sort_order' => 'desc',
        ], 'get');
        $result = $this->aftersalesService->afterSalesRecord($filter, request()->userId);
        return $this->success([
            'filter_result' => $result["list"],
            'filter' => $filter,
            'total' => $result["count"],
        ]);
    }

    /**
     * 查看售后记录
     * @return Response
     * @throws \app\common\exceptions\ApiException
     */
    public function detail(): Response
    {
        $id = input('id/d', 0);
        $item = $this->aftersalesService->getDetail($id);
        return $this->success([
            'item' => $item,
        ]);
    }

    /**
     * 查看售后log记录
     * @return Response
     * @throws \app\common\exceptions\ApiException
     */
    public function detailLog(): Response
    {
        $id = input('id/d', 0);
        $item = $this->aftersalesService->getDetailLog($id);
        return $this->success([
            'item' => $item,
        ]);
    }

    /**
     * 提交售后反馈记录
     * @return Response
     * @throws \app\common\exceptions\ApiException
     */
    public function feedback(): Response
    {
        $id = input('id/d', 0);
        $data = $this->request->only([
            'id' => $id,
            "log_info" => "",
            "return_pic/a" => [],
            "logistics_name" => "",
            "tracking_no" => "",
        ], 'post');
        $result = $this->aftersalesService->submitFeedbackRecord($id, $data, request()->userId);
        return $result ? $this->success(/** LANG */"已更新您的退换货信息") : $this->error(/** LANG */'未知错误，提交失败');
    }

    /**
     * 撤销申请售后
     * @return Response
     */
    public function cancel(): Response
    {
        $id = input('aftersale_id/d', 0);
        $result = $this->aftersalesService->cancel($id);
        return $result ? $this->success(/** LANG */"撤销成功") : $this->error(/** LANG */'撤销失败');
    }
}
