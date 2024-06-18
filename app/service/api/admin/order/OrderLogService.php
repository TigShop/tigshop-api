<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 订单日志
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\order;

use app\model\order\Order;
use app\model\order\OrderLog;
use app\model\order\OrderSplitLog;
use app\service\core\BaseService;
use app\validate\order\OrderLogValidate;
use exceptions\ApiException;
use log\AdminLog;
use utils\Time;

/**
 * 订单日志服务类
 */
class OrderLogService extends BaseService
{
    protected OrderLog $orderLogModel;
    protected OrderLogValidate $orderLogValidate;

    public function __construct(OrderLog $orderLogModel)
    {
        $this->orderLogModel = $orderLogModel;
    }

    /**
     * 获取筛选结果
     *
     * @param array $filter
     * @return array
     */
    public function getFilterResult(array $filter): array
    {
        $query = $this->filterQuery($filter)->append(["operator"]);
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
        $query = $this->orderLogModel->query();
        // 处理筛选条件

        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->where('description', 'like', '%' . $filter['keyword'] . '%');
        }
        // 订单检索
        if (isset($filter['order_id']) && !empty($filter['order_id'])) {
            $parent_order_ids = $this->getParentOrderIds($filter['order_id']);
            $query->whereIn('order_id', $parent_order_ids);
        }

        if (isset($filter['sort_field'], $filter['sort_order']) && !empty($filter['sort_field']) && !empty($filter['sort_order'])) {
            $query->order($filter['sort_field'], $filter['sort_order']);
        }
        return $query;
    }

    /**
     * 执行订单日志添加
     * @param array $data
     * @return bool
     * @throws ApiException
     */
    public function addOrderLog(array $data):bool
    {
        if (empty($data['order_id']) && empty($data['order_sn'])) {
            throw new ApiException(/** LANG */'参数错误:订单ID和订单编号不能同时为空');
        }
        $data['user_id'] = request()->userId > 0 ? request()->userId : 0;
        $data['admin_id'] = request()->adminUid > 0 ? request()->adminUid : 0;
        $data['log_time'] = Time::now();
        $data['order_id'] = $data['order_id'] ?? 0;
        $data['order_sn'] = $data['order_sn'] ?? '';

        if ($data['order_id'] > 0 || !empty($data['order_sn'])){
            $data['shop_id'] = Order::where('order_id|order_sn', $data['order_id'] > 0 ? $data['order_id'] : $data['order_sn'])
                ->findOrEmpty()->shop_id ?? 0;
        }
        $result = OrderLog::create($data);
        // 如果是后台操作
        if (request()->adminUid > 0) {
            AdminLog::add('订单：' . $data['order_sn'] . '，' . $data['description']);
        }
        return $result !== false;
    }
    // 添加拆分日志
    public function addSplitLog(int $order_id, int $new_order_id)
    {
        $split_log = new OrderSplitLog();
        $split_log->parent_order_id = $order_id;
        $split_log->order_id = $new_order_id;
        $split_log->split_time = Time::now();
        $split_log->save();
    }
    /**
     * 根据订单ID获取所有父级订单ID（包括自己）
     *
     * @param int $orderId 订单ID
     * @return array 包含所有父级订单ID的数组
     */
    public function getParentOrderIds($orderId): array
    {
        $parent_order_ids = [];
        $this->recursiveParentIds($orderId, $parent_order_ids);
        return $parent_order_ids;
    }
    /**
     * 递归查询父级订单ID
     *
     * @param int $order_id 订单ID
     * @param array $parent_order_ids 已收集的父级订单ID数组
     */
    protected function recursiveParentIds($order_id, array &$parent_order_ids)
    {
        array_unshift($parent_order_ids, $order_id);
        $parentOrderId = OrderSplitLog::where('order_id', $order_id)->value('parent_order_id');
        if ($parentOrderId && !in_array($parentOrderId, $parent_order_ids)) {
            $this->recursiveParentIds($parentOrderId, $parent_order_ids);
        }
    }
}
