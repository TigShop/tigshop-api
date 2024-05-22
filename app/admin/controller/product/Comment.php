<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 评论晒单
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\admin\controller\product;

use app\admin\AdminBaseController;
use app\service\api\admin\product\CommentService;
use think\App;

/**
 * 评论晒单控制器
 */
class Comment extends AdminBaseController
{
    protected CommentService $commentService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param CommentService $commentService
     */
    public function __construct(App $app, CommentService $commentService)
    {
        parent::__construct($app);
        $this->commentService = $commentService;
        $this->checkAuthor('commentManage'); //权限检查
    }

    /**
     * 列表页面
     *
     * @return \think\Response
     */
    public function list(): \think\Response
    {
        $filter = $this->request->only([
            'is_show/d' => -1,
            'keyword' => '',
            'page/d' => 1,
            'size/d' => 15,
            'sort_field' => 'comment_id',
            'sort_order' => 'desc',
            "is_showed/d" => -1, // 有无晒单
        ], 'get');

        $filterResult = $this->commentService->getFilterResult($filter);
        $total = $this->commentService->getFilterCount($filter);

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
        $item = $this->commentService->getDetail($id);
        return $this->success([
            'item' => $item,
        ]);
    }

    /**
     * 执行添加或更新操作
     *
     * @return \think\Response
     */
    public function create(): \think\Response
    {
        $id = input('id/d', 0);
        $data = $this->request->only([
            'comment_id' => $id,
            'username' => '',
            'avatar' => '',
            'comment_rank/d' => 1,
            'comment_tag/a' => [],
            'content' => '',
            'show_pics' => '',
            'sort_order/d' => 50,
            'is_recommend/d' => 0,
            'is_top/d' => 0,
            'product_id/d' => 0,
            'order_id/d' => 0,
            'order_item_id/d' => 0,
        ], 'post');

        $result = $this->commentService->updateComment($id, $data, true);
        if ($result) {
            return $this->success('评论晒单更新成功');
        } else {
            return $this->error('评论晒单更新失败');
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
            'comment_id' => $id,
            'username' => '',
            'avatar' => '',
            'comment_rank/d' => 1,
            'comment_tag/a' => [],
            'content' => '',
            'show_pics' => '',
            'sort_order/d' => 50,
            'is_recommend/d' => 0,
            'is_top/d' => 0,
            'product_id/d' => 0,
            'order_id/d' => 0,
            'order_item_id/d' => 0,
        ], 'post');

        $result = $this->commentService->updateComment($id, $data, false);
        if ($result) {
            return $this->success('评论晒单更新成功');
        } else {
            return $this->error('评论晒单更新失败');
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

        if (!in_array($field,
            ['content', 'is_recommend', 'sort_order', 'status', 'is_top', 'comment_rank', 'is_show'])) {
            return $this->error('#field 错误');
        }

        $data = [
            'comment_id' => $id,
            $field => input('val'),
        ];

        $this->commentService->updateCommentField($id, $data);

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
        $this->commentService->deleteComment($id);
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
                $this->commentService->deleteComment($id);
            }
            return $this->success('批量操作执行成功！');
        } else {
            return $this->error('#type 错误');
        }
    }

    /**
     * 回复评论
     * @return \think\Response
     */
    public function replyComment(): \think\Response
    {
        $data = $this->request->only([
            'comment_id/d' => 0,
            'content' => '',
        ], 'post');
        $result = $this->commentService->replyComment($data);
        if ($result) {
            return $this->success("评论回复成功");
        } else {
            return $this->error('评论回复失败');
        }
    }
}
