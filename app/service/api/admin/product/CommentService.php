<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 评论晒单
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\product;

use app\model\authority\AdminUser;
use app\model\order\Order;
use app\model\order\OrderItem;
use app\model\product\Comment;
use app\model\user\User;
use app\service\api\admin\BaseService;
use app\service\api\admin\order\OrderStatusService;
use app\service\api\admin\user\UserService;
use app\validate\product\CommentValidate;
use exceptions\ApiException;
use log\AdminLog;
use think\facade\Db;
use utils\Config;
use utils\Time;

/**
 * 评论晒单服务类
 */
class CommentService extends BaseService
{
    protected Comment $commentModel;
    protected CommentValidate $commentValidate;

    public function __construct(Comment $commentModel)
    {
        $this->commentModel = $commentModel;
    }

    /**
     * 获取筛选结果
     *
     * @param array $filter
     * @return array
     */
    public function getFilterResult(array $filter): array
    {
        $query = $this->filterQuery($filter)->with(["product", "reply" => function ($query) {
            $query->field('comment_id,user_id,username,content,add_time,parent_id');
        }]);
        $result = $query->page($filter['page'], $filter['size'])->select();
        return $result->toArray();
    }

    /**
     * 获取筛选结果数量
     *
     * @param array $filter
     * @return int
     */
    public function getFilterCount(array $filter): int
    {
        $query = $this->filterQuery($filter);
        $count = $query->count();
        return $count;
    }

    /**
     * 筛选查询
     *
     * @param array $filter
     * @return object
     */
    protected function filterQuery(array $filter): object
    {
        $query = $this->commentModel->query();
        // 处理筛选条件

        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->where('content', 'like', '%' . $filter['keyword'] . '%');
        }

        if (isset($filter['is_show']) && $filter['is_show'] > -1) {
            $query->where('is_show', $filter['is_show']);
        }

        if (isset($filter['product_id'])) {
            $query->where('product_id', $filter['product_id']);
        }
        if (!empty($filter['product_ids']) && is_array($filter['product_ids'])) {
            $query->whereIn('product_id', $filter['product_ids']);
        }

        if (isset($filter['type'])) {
            if ($filter['type'] == 2) {
                $query->where('comment_rank', '>=', 4);
            } elseif ($filter['type'] == 3) {
                $query->where('comment_rank', '=', 3);
            } elseif ($filter['type'] == 4) {
                $query->where('comment_rank', '<=', 2);
            } elseif ($filter['type'] == 5) {
                $query->where('show_pics', '<>', '');
            }
        }

        // 有无晒单
        if (isset($filter['is_showed']) && $filter["is_showed"] != -1) {
            $query->where('is_showed', $filter["is_showed"]);
        }

        if (isset($filter['sort_field'], $filter['sort_order']) && !empty($filter['sort_field']) && !empty($filter['sort_order'])) {
            $query->order($filter['sort_field'], $filter['sort_order']);
        }

        if (isset($filter['user_id']) && $filter['user_id'] > 0) {
            $query->where('user_id', $filter['user_id']);
        }

        $query->where('parent_id', 0);
        return $query;
    }

    /**
     * 获取详情
     *
     * @param int $id
     * @return array
     * @throws ApiException
     */
    public function getDetail(int $id): array
    {
        $result = $this->commentModel->with([
            "reply" => function ($query) {
                $query->field('comment_id,user_id,username,content,add_time,parent_id');
            },
        ])->where('comment_id', $id)->find();

        if (!$result) {
            throw new ApiException('评论晒单不存在');
        }

        return $result->toArray();
    }

    /**
     * 获取名称
     *
     * @param int $id
     * @return string|null
     */
    public function getName(int $id): ?string
    {
        return $this->commentModel::where('comment_id', $id)->value('content');
    }

    /**
     * 执行评论晒单添加或更新
     *
     * @param int $id
     * @param array $data
     * @param bool $isAdd
     * @return int|bool
     * @throws ApiException
     */
    public function updateComment(int $id, array $data, bool $isAdd = false)
    {
        validate(CommentValidate::class)->only(array_keys($data))->check($data);
        $data["user_id"] = request()->userId ?? 0;
        $data["shop_id"] = request()->shopId ?? 0;
        if (!empty($data["show_pics"])) {
            $data["is_showed"] = 1;
        }
        if ($isAdd) {
            $this->commentModel->save($data);
            $result = $this->commentModel->getKey();
            AdminLog::add('新增评论晒单:' . $data['content']);
            return $result !== false;
        } else {
            if (!$id) {
                throw new ApiException('#id错误');
            }
            $result = $this->commentModel->where('comment_id', $id)->save($data);
            AdminLog::add('更新评论晒单:' . $this->getName($id));

            return $result !== false;
        }
    }

    /**
     * 更新单个字段
     *
     * @param int $id
     * @param array $data
     * @return int|bool
     * @throws ApiException
     */
    public function updateCommentField(int $id, array $data)
    {
        validate(CommentValidate::class)->only(array_keys($data))->check($data);
        if (!$id) {
            throw new ApiException('#id错误');
        }
        $result = $this->commentModel::where('comment_id', $id)->save($data);
        AdminLog::add('更新评论晒单:' . $this->getName($id));
        return $result !== false;
    }

    /**
     * 删除评论晒单
     *
     * @param int $id
     * @return bool
     */
    public function deleteComment(int $id): bool
    {
        if (!$id) {
            throw new ApiException('#id错误');
        }
        $get_name = $this->getName($id);
        $result = $this->commentModel::destroy($id);

        if ($result) {
            AdminLog::add('删除评论晒单:' . $get_name);
        }

        return $result !== false;
    }

    public function getProductCommentRankDetail(int $id): array
    {
        $item = $this->commentModel::field('COUNT(*) AS total')
            ->field('AVG(comment_rank) AS average_rank')
            ->field('(COUNT(CASE WHEN comment_rank >=4 THEN 1 END) / COUNT(*) * 100) AS good_percent')
            ->whereIn('product_id', $this->getProductIds($id))
//            ->where('product_id', '>', 0)
            ->find();
        $item->average_rank = $item->average_rank > 0 ? intval($item->average_rank) : 0;
        $item->good_percent = $item->good_percent > 0 ? intval($item->good_percent) : 0;
        return $item ? $item->toArray() : [];

    }

    public function getProductCommentDetail(int $id): array
    {
        $item = $this->commentModel::field('COUNT(*) AS total')
            ->field('COUNT(CASE WHEN comment_rank >=4 THEN 1 END) AS good_count')
            ->field('COUNT(CASE WHEN comment_rank = 3 AND comment_rank <= 4 THEN 1 END) AS moderate_count')
            ->field('COUNT(CASE WHEN comment_rank <= 2 THEN 1 END) AS bad_count')
            ->field('COUNT(CASE WHEN show_pics <>"" THEN 1 END) AS show_count')
            ->whereIn('product_id', $this->getProductIds($id))
            ->where('product_id', '>', 0)
            ->group('product_id')
            ->findOrEmpty();
        if (!isset($item->total)) {
            $item->total = 0;
            $item->bad_count = 0;
            $item->good_count = 0;
            $item->moderate_count = 0;
            $item->show_count = 0;
        }
        $item->good_percent = $item && $item->total > 0 ? intval($item->good_count / $item->total * 100) : 0;
        $item->moderate_percent = $item && $item->total > 0 ? intval($item->moderate_count / $item->total * 100) : 0;
        $item->bad_percent = $item && $item->total > 0 ? intval($item->bad_count / $item->total * 100) : 0;
        return $item ? $item->toArray() : [];
    }

    // 查看有没有被关联的商品，返回ids
    public function getProductIds(int $id): array
    {
        $ids = Db::table('product_related')->where('product_id', $id)->column('related_product_id');
        array_unshift($ids, $id);
        return $ids;
    }

    /**
     * 获取商品评分
     * @param int $id
     * @return int
     */
    public function getProductCommentRank(int $id): int
    {
        $rank = $this->commentModel::where('product_id', $id)
            ->where('parent_id', 0)
            ->where('status', 1)
            ->avg('comment_rank');

        $rank = ceil($rank) == 0 ? 5 : ceil($rank);

        return $rank;
    }

    /**
     * 获取评论列表
     * @param array $filter
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getProductCommentList(array $filter): array
    {
        $filter['product_ids'] = $this->getProductIds($filter['product_id']);
        $filter['size'] = 10;
        $filter['page'] = $filter['page'] ? $filter['page'] : 1;
        unset($filter['product_id']);
        $query = $this->filterQuery($filter);
        $result = $query->page($filter['page'], $filter['size'])->select();
        return $result->toArray();
    }

    /**
     * 获取商品评论数
     * @param array $filter
     * @return int
     * @throws \think\db\exception\DbException
     */
    public function getProductCommentCount(array $filter): int
    {
        $filter['product_ids'] = $this->getProductIds($filter['product_id']);
        unset($filter['product_id']);
        $query = $this->filterQuery($filter);
        $count = $query->count();
        return $count;
    }

    /**
     * 回复评论
     * @param array $data
     * @return bool
     */
    public function replyComment(array $data): bool
    {
        // 查看是否有回复
        $comment = Comment::where("parent_id", $data["comment_id"])->find();
        $comment = $comment ? $comment->toArray() : [];
        $username = AdminUser::find(request()->adminUid)->username;
        $arr_reply = [
            "user_id" => request()->adminUid,
            'username' => $username,
            'content' => $data["content"],
            "add_time" => Time::now(),
        ];
        if ($comment) {
            // 更新回复
            $reply_res = Comment::where("parent_id", $data["comment_id"])->save($arr_reply);
        } else {
            // 添加回复
            $arr_reply["parent_id"] = $data["comment_id"];
            $reply_res = Comment::create($arr_reply);
            // 修改主评论的状态
            Comment::where("comment_id", $data["comment_id"])->update(["status" => 1]);
        }
        if (!$reply_res) {
            return false;
        }
        return true;
    }

    /**
     * PC 评论数量
     * @param int $user_id
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function getSubNum(int $user_id): array
    {
        // 待评价订单
        $result['await_comment'] = Order::where('user_id', $user_id)->awaitComment()->where("is_del", 0)->count();
        // 待晒单
        $result["show_pics"] = Comment::hasWhere("orderInfo", function ($query) use ($user_id) {
            $query->where("user_id", $user_id)->completed()->where("is_del", 0);
        })->where(["is_showed" => 0, "parent_id" => 0])->where("comment.user_id", $user_id)->count();
        // 已评价
        $result["commented"] = Order::where(['user_id' => $user_id, "is_del" => 0, "comment_status" => 1])->count();
        return $result;
    }

    /**
     * 晒单列表
     * @param array $filter
     * @param int $user_id
     * @return array
     */
    public function getShowPics(array $filter, int $user_id): array
    {
        $query = Order::with(["items", "user"])->where(["is_del" => 0, "order.user_id" => $user_id])->completed()
            ->append(['order_status_name', "user_address", "shipping_status_name", "pay_status_name"]);

        if (isset($filter["is_showed"]) && $filter["is_showed"] != -1) {
            $query = $query->hasWhere("comment", function ($query) use ($user_id, $filter) {
                $query->where(["is_showed" => $filter["is_showed"]])->where("user_id", $user_id);
            });
        }

        $count = $query->count();
        $result = $query->page($filter['page'] ?? 1, $filter['size'] ?? 15)
            ->order($filter['sort_field'] ?? 'order_id', $filter['sort_order'] ?? 'desc')
            ->select();
        foreach ($result as $item) {
            $orderStatusService = new OrderStatusService();
            $item->available_actions = $orderStatusService->getAvailableActions($item);
        }
        return [
            "count" => $count,
            "list" => $result,
        ];
    }

    /**
     * 添加评价 / 晒单
     * @param array $data
     * @param int $user_id
     * @return bool
     * @throws ApiException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function updateEvaluate(array $data, int $user_id): bool
    {
        $data["user_id"] = $user_id;
        $user_info = User::field("username,avatar")->find($user_id);
        if (empty($user_info)) {
            throw new ApiException('该用户不存在');
        }
        $data["username"] = $user_info->username;
        $data["avatar"] = $user_info->avatar;
        // 审核状态
        $data["status"] = Config::get("comment_check") ? 0 : 1;
        // 是否晒单
        $data["is_showed"] = !empty($data["show_pics"]) ? 1 : 0;
        // 判断是否已评价
        $is_comment = Comment::where(["order_id" => $data["order_id"], "order_item_id" => $data["order_item_id"], "user_id" => $user_id, "parent_id" => 0])->find();
        if (empty($is_comment)) {
            // 未评价
            $result = Comment::create($data);
        } else {
            // 已评价  -- 判断是否晒单
            if ($is_comment->is_showed) {
                // 已晒单
                throw new ApiException('您已评价完成，不能重复评价');
            } else {
                // 未晒单
                if (empty($data['show_pics'])) {
                    throw new ApiException('请上传晒单图片');
                }
                $result = $is_comment->save(["show_pics" => $data["show_pics"], "is_showed" => 1]);
            }
        }
        $show_send_point = Config::get("show_send_point");
        $comment_send_point = Config::get("comment_send_point");
        if ($result !== false) {
            if (!empty($is_comment)) {
                // 单独晒单，增加晒单积分
                if ($show_send_point > 0) {
                    app(UserService::class)->changesInFunds($user_id, [
                        "change_desc" => "晒单送积分",
                        "type_points" => 1,
                        "points" => $show_send_point,
                    ]);
                }
            } else {
                // 首次评价 -- 判断是否晒单
                if ($comment_send_point > 0) {
                    app(UserService::class)->changesInFunds($user_id, [
                        "change_desc" => "评论送积分",
                        "type_points" => 1,
                        "points" => $comment_send_point,
                    ]);
                }

                if (!empty($data["show_pics"]) && $show_send_point > 0) {
                    app(UserService::class)->changesInFunds($user_id, [
                        "change_desc" => "晒单送积分",
                        "type_points" => 1,
                        "points" => $show_send_point,
                    ]);
                }
            }

            // 更新订单评论状态
            $order_item = OrderItem::where(["order_id" => $data["order_id"], "user_id" => $user_id])->column("item_id");
            $comment_item = Comment::where(["order_id" => $data["order_id"], "user_id" => $user_id, "parent_id" => 0])
                ->whereIn("order_item_id", $order_item)->count();
            if ($comment_item == count($order_item)) {
                // 该订单的商品已全部评价
                Order::find($data["order_id"])->save(["comment_status" => 1]);
            }
        }
        return $result !== false;
    }

    /**
     * 评价/晒单详情
     * @param int $id
     * @return object
     * @throws ApiException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getCommentDetail(int $id): object
    {
        $order_info = Order::with(["items", "user"])->find($id);
        if (empty($order_info)) {
            throw new ApiException('订单不存在');
        }

        foreach ($order_info->items as $item) {
            $item->comment_info = Comment::where(["order_id" => $id, "order_item_id" => $item->item_id, "parent_id" => 0, "user_id" => $item->user_id])
                ->field("comment_id,comment_rank,comment_tag,content,show_pics,shop_id,is_showed")
                ->findOrEmpty()
                ->toArray();
            $item->comment_info = !empty($item->comment_info) ? $item->comment_info : "";
        }
        return $order_info;
    }

}
