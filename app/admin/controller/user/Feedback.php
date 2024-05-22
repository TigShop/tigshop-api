<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 会员留言
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\admin\controller\user;

use app\admin\AdminBaseController;
use app\service\api\admin\user\FeedbackService;
use think\App;

/**
 * 会员留言控制器
 */
class Feedback extends AdminBaseController
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
        $this->feedbackService = $feedbackService;
        $this->checkAuthor('feedbackManage'); //权限检查
    }

    /**
     * 列表页面
     *
     * @return \think\Response
     */
    public function list(): \think\Response
    {
        $filter = $this->request->only([
            'keyword' => '',
            'page/d' => 1,
            'size/d' => 15,
            'sort_field' => 'id',
            'sort_order' => 'desc',
            'type/d' => -1,
        ], 'get');

        $filterResult = $this->feedbackService->getFilterResult($filter);
        $total = $this->feedbackService->getFilterCount($filter);

        return $this->success([
            'filter_result' => $filterResult,
            'filter' => $filter,
            'total' => $total,
        ]);
    }

    /**
     * 详情
     *
     * @return \think\Response
     */
    public function detail(): \think\Response
    {
        $id = input('id/d', 0);
        $item = $this->feedbackService->getDetail($id);
        return $this->success([
            'item' => $item,
        ]);
    }

    /**
     * 执行添加或操作
     *
     * @return \think\Response
     */
    public function create(): \think\Response
    {
        $id = input('id/d', 0);
        $data = $this->request->only([
            'id' => $id,
            'title' => '',
            'parent_id/d' => 0,
            'email' => '',
            'content' => '',
            'mobile' => '',
            'feedback_pics' => [],
            'product_id/d' => 0,
            'order_id/d' => 0,
            "complaint_info" => '',
            'store_id/d' => 0,
        ], 'post');

        $result = $this->feedbackService->updateFeedback($id, $data, true);
        if ($result) {
            return $this->success('会员留言添加成功');
        } else {
            return $this->error('会员留言更新失败');
        }
    }

    /**
     * 执行添加或更新操作
     *
     * @return \think\Response
     */
    public function update(): \think\Response
    {
        $id = input('id/d', 0);
        $data = $this->request->only([
            'id' => $id,
            'title' => '',
            'parent_id/d' => 0,
            'email' => '',
            'content' => '',
            'mobile' => '',
            'feedback_pics' => [],
            'product_id/d' => 0,
            'order_id/d' => 0,
            "complaint_info" => '',
            'store_id/d' => 0,
        ], 'post');

        $result = $this->feedbackService->updateFeedback($id, $data, false);
        if ($result) {
            return $this->success('会员留言更新成功');
        } else {
            return $this->error('会员留言更新失败');
        }
    }

    /**
     * 更新单个字段
     *
     * @return \think\Response
     */
    public function updateField(): \think\Response
    {
        $id = input('id/d', 0);
        $field = input('field', '');

        if (!in_array($field, ['title', 'sort_order', 'is_show'])) {
            return $this->error('#field 错误');
        }

        $data = [
            'id' => $id,
            $field => input('val'),
        ];

        $this->feedbackService->updateFeedbackField($id, $data);

        return $this->success('更新成功');
    }

    /**
     * 删除
     *
     * @return \think\Response
     */
    public function del(): \think\Response
    {
        $id = input('id/d', 0);
        $this->feedbackService->deleteFeedback($id);
        return $this->success('指定项目已删除');
    }

    /**
     * 批量操作
     *
     * @return \think\Response
     */
    public function batch(): \think\Response
    {
        if (empty(input('ids')) || !is_array(input('ids'))) {
            return $this->error('未选择项目');
        }

        if (input('type') == 'del') {
            foreach (input('ids') as $key => $id) {
                $id = intval($id);
                $this->feedbackService->deleteFeedback($id);
            }
            return $this->success('批量操作执行成功！');
        } else {
            return $this->error('#type 错误');
        }
    }
}
