<?php
//**---------------------------------------------------------------------+
//** 通用接口控制器文件 -- 留言咨询
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\index\controller\user;

use app\index\IndexBaseController;
use app\service\api\admin\user\FeedbackService;
use think\App;
use think\Response;

class Feedback extends IndexBaseController
{
    protected FeedbackService $feedbackService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param FeedbackService $feedbackService
     */
    public function __construct(App $app, FeedbackService $feedbackService)
    {
        parent::__construct($app);
        $this->checkLogin();
        $this->feedbackService = $feedbackService;
    }

    /**
     * 订单咨询/留言列表
     * @return Response
     */
    public function list(): Response
    {
        $filter = $this->request->only([
            'page/d' => 1,
            'size/d' => 15,
            'is_order/d' => -1,
            'order_id/d' => 0,
            'product_id/d' => 0,
            'sort_field' => 'id',
            'sort_order' => 'desc',
        ], 'get');
        $filter["user_id"] = request()->userId;
        $result = $this->feedbackService->orderInquiryList($filter);
        return $this->success([
            'filter_result' => $result["list"],
            'filter' => $filter,
            'total' => $result["count"],
        ]);
    }

    /**
     * 提交留言
     * @return Response
     */
    public function submit(): Response
    {
        $data = $this->request->only([
            'type/d' => 0,
            'title' => '',
            'content' => '',
            'product_id/d' => 0,
            'order_id/d' => 0,
            'complaint_info' => '',
            'store_id/d' => 0,
            'email' => '',
            'mobile' => '',
            'feedback_pics/a' => [],
        ], 'post');
        $result = $this->feedbackService->submitFeedback($data, request()->userId);
        return $result ? $this->success(/** LANG */"提交成功") : $this->error(/** LANG */'未知错误,提交失败');
    }
}
