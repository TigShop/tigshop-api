<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 发票申请
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\finance;

use app\model\finance\OrderInvoice;
use app\model\order\Order;
use app\service\core\BaseService;
use exceptions\ApiException;

/**
 * 发票申请服务类
 */
class OrderInvoiceService extends BaseService
{
    public function __construct()
    {
    }

    /**
     * 获取筛选结果
     *
     * @param array $filter
     * @return array
     */
    public function getFilterResult(array $filter): array
    {
        $query = $this->filterQuery($filter)->with(["user", "order_info"])->append(['invoice_type_name', 'status_name', "title_type_name"]);
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
    public function filterQuery(array $filter): object
    {
        $query = OrderInvoice::query();
        $query = $query->join('order', 'order.order_id = order_invoice.order_id')
            ->join('user', 'user.user_id = order_invoice.user_id')
            ->field("order_invoice.*");
        // 处理筛选条件

        // 关键词检索 -- 会员名称 + 公司名称 + 订单编号
        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->keyword($filter['keyword']);
        }

        // 发票类型
        if (isset($filter['invoice_type']) && !empty($filter['invoice_type'])) {
            $query->where('order_invoice.invoice_type', $filter['invoice_type']);
        }

        // 发票抬头
        if (isset($filter['title_type']) && !empty($filter['title_type'])) {
            $query->where('order_invoice.title_type', $filter['title_type']);
        }

        // 用户
        if (isset($filter['user_id']) && $filter['user_id'] > 0) {
            $query->where('order_invoice.user_id', $filter['user_id']);
        }

        // 发票状态
        if (isset($filter['status']) && $filter['status'] != -1) {
            $query->where('order_invoice.status', $filter['status']);
        }

        // 店铺检索
        if (isset($filter['shop_id']) && $filter["shop_id"] != -1) {
            $query->where('order.shop_id', $filter["shop_id"]);
        }

        // 店铺分类
        if (isset($filter["store_type"])) {
            if ($filter['store_type']) {
                $query->where('order.shop_id', ">", 0);
            } else {
                $query->where('order.shop_id', 0);
            }
        }

        if (isset($filter['sort_field'], $filter['sort_order']) && !empty($filter['sort_field']) && !empty($filter['sort_order'])) {
            $query->order($filter['sort_field'], $filter['sort_order']);
        }
        return $query;
    }

    /**
     * 获取详情
     *
     * @param int $id
     * @return OrderInvoice
     * @throws ApiException
     */
    public function getDetail(int $id): OrderInvoice
    {
        $result = OrderInvoice::with(["user", "order_info", "user_invoice"])->where('id', $id)->append(['invoice_type_name', 'status_name', "title_type_name"])->find();
        if (!$result) {
            throw new ApiException(/** LANG */'发票申请不存在');
        }
        return $result;
    }

    /**
     * 添加发票申请
     * @param array $data
     * @return int
     */
    public function createOrderInvoice(array $data): int
    {
        $result = OrderInvoice::create($data);
        return $result->getKey();
    }

    /**
     * 执行发票申请更新
     *
     * @param int $id
     * @param array $data
     * @return bool
     * @throws ApiException
     */
    public function updateOrderInvoice(int $id, array $data): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = OrderInvoice::where('id', $id)->save($data);

        // 修改订单表内发票信息
        if ($result !== false) {
            $order_id = OrderInvoice::find($id)->order_id;
            $order = Order::findOrEmpty($order_id);
            $invoice_data = $order->invoice_data;
            $invoice_data['status'] = $data['status'];
            $order->invoice_data = $invoice_data;
            $order->save();
        }
        return $result !== false;
    }
    /**
     * 更新单个字段
     *
     * @param int $id
     * @param array $data
     * @return int|bool
     * @throws ApiException
     */
    public function updateOrderInvoiceField(int $id, array $data)
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = OrderInvoice::where('id', $id)->save($data);
        return $result !== false;
    }

    /**
     * 删除发票申请
     *
     * @param int $id
     * @return bool
     */
    public function deleteOrderInvoice(int $id): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = OrderInvoice::destroy($id);
        return $result !== false;
    }

    /**
     * PC端 - 添加修改订单发票申请
     * @param int $id
     * @param int $user_id
     * @param array $data
     * @return bool
     * @throws ApiException
     */
    public function updateOrderInvoicePc(int $id, int $user_id, array $data, bool $isAdd = false)
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        if (isset($data["title_type"])) {
            $order_invoice = [
                "user_id" => $user_id,
                "order_id" => $id,
                "invoice_type" => $data["invoice_type"],
                "title_type" => $data["title_type"],
                "company_name" => $data["company_name"],
                "invoice_content" => $data["invoice_content"],
                "amount" => $data["amount"],
                "mobile" => $data["mobile"],
                "email" => $data["email"],
                "status" => 0,
            ];
            if ($data["title_type"] == 2) {
                // 企业
                $order_invoice["company_code"] = $data["company_code"];
                $order_invoice["company_address"] = $data["company_address"];
                $order_invoice["company_phone"] = $data["company_phone"];
                $order_invoice["company_bank"] = $data["company_bank"];
                $order_invoice["company_account"] = $data["company_account"];
            }
        }
        if ($isAdd) {
            $result = OrderInvoice::create($order_invoice);
        } else {
            $order_invoice_info = OrderInvoice::where(["order_id" => $id, "user_id" => $user_id])->find();
            if (empty($order_invoice_info)) {
                throw new ApiException(/** LANG */'该发票申请不存在');
            }
            if ($order_invoice_info->status == 1) {
                throw new ApiException(/** LANG */'该发票申请已通过审核，不能修改');
            }
            if (!empty($order_invoice_info->apply_reply)) {
                $order_invoice["apply_reply"] = "";
            }
            $result = $order_invoice_info->save($order_invoice);
        }

        // 订单记录发票信息
        if ($result !== false) {
            $res = Order::find($id)->save(["invoice_data" => $order_invoice]);
            if ($res) {
                return true;
            }
        }
        return false;
    }

    /**
     * PC端 - 获取订单发票详情
     * @param int $id
     * @return array
     * @throws ApiException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getOrderInvoiceDetail(int $id,int $user_id): array
    {
        $result = OrderInvoice::with(["order_info"])
            ->where(["order_id" => $id, "user_id" => $user_id])
            ->append(['invoice_type_name', 'status_name', "title_type_name"])
            ->findOrEmpty()->toArray();
        // 查询订单是否有发票信息
        $invoice_data = Order::findOrEmpty($id)->invoice_data;
        if (empty($result)) {
            if (!empty($invoice_data)) {
                //生成发票申请
                OrderInvoice::create($invoice_data);
                return $invoice_data;
            } else {
                throw new ApiException(/** LANG */'发票申请不存在');
            }
        }
        return $result;
    }

}
