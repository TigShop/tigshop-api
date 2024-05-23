<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 退换货
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\order;

use app\common\exceptions\ApiException;
use app\common\log\AdminLog;
use app\common\utils\Time;
use app\model\authority\AdminUser;
use app\model\order\Aftersales;
use app\model\order\AftersalesItem;
use app\model\order\AftersalesLog;
use app\model\order\Order;
use app\model\order\OrderItem;
use app\model\user\User;
use app\service\api\admin\BaseService;
use app\service\api\admin\finance\RefundApplyService;
use app\validate\order\AftersalesValidate;
use think\facade\Db;

/**
 * 退换货服务类
 */
class AftersalesService extends BaseService
{
    protected AftersalesValidate $aftersalesValidate;

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
        $query = $this->filterQuery($filter)->with([
            'aftersales_items',
            'aftersales_items.items',
            'orderSn'
        ])->append(['aftersales_type_name', "status_name", 'user_name', 'shipping_time']);
        $result = $query->page($filter['page'], $filter['size'])->select();
        return $result->toArray();
    }

    public function getAfterSalesCount(int $order_id): int
    {
        return Aftersales::whereIn('status', Aftersales::VALID_STATUS)->where('order_id', $order_id)->count();
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
        $query = Aftersales::query();
        // 处理筛选条件

        // 订单号或姓名
        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->keywords($filter['keyword']);
        }

        // 状态
        if (isset($filter['status']) && !empty($filter['status'])) {
            $query->where('status', $filter['status']);
        }
        // 申请类型
        if (isset($filter['aftersale_type']) && !empty($filter['aftersale_type'])) {
            $query->where('aftersale_type', $filter['aftersale_type']);
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
     * @return array
     * @throws ApiException
     */
    public function getDetail(int $id): array
    {
        $result = Aftersales::with(["aftersales_items" => ['items'], "aftersales_log", 'orders', 'refund'])
            ->append(['aftersales_type_name', "status_name", 'user_name', 'shipping_time'])->findOrEmpty($id);
        $result->can_Cancel = $result->canCancel();
        $result->step_status = $this->getStepStatus($result);

        if (!$result) {
            throw new ApiException('退换货不存在');
        }

        return $result->toArray();
    }

    /**
     * 获取详情
     *
     * @param int $id
     * @return array
     * @throws ApiException
     */
    public function getDetailLog(int $id): object
    {
        $result = AftersalesLog::where('aftersale_id', $id)->order('log_id', 'asc')->select();
        return $result;
    }

    /**
     * 撤销申请
     * @return array
     */
    public function cancel(int $id): bool
    {
        $aftersale = Aftersales::find($id);
        if (!$aftersale) {
            throw new ApiException('记录不存在');
        }
        if (!$aftersale->canCancel()) {
            throw new ApiException('该状态下不能取消申请');
        }
        $aftersale->status = Aftersales::STATUS_CANCEL;
        $result = $aftersale->save();
        if ($result) {
            $this->addAftersaleLog($aftersale['aftersale_id'], [
                'status' => $aftersale->status,
            ]);
        }
        return $result;
    }

    /**
     * 售后步骤状态
     * @param object $aftersales
     * @return array
     */
    public function getStepStatus(object $aftersales): array
    {
        // 初始化所有动作为false
        $current = 0;
        $status = 'process';
        $steps = [];
        $steps[0] = [
            'title' => '提交申请',
            'active' => true,
            'description' => $aftersales->add_time,
        ];
        $steps[1] = [
            'title' => "客服审核",
            'active' => false,
            'description' => '',
        ];
        if ($aftersales->type == Aftersales::AFTERSALES_TYPE_PAYRETURN) {
            $steps[2] = [
                'title' => "已完成",
                'active' => false,
                'description' => '',
            ];
        } else {
            $steps[2] = [
                'title' => "售后收货",
                'active' => false,
                'description' => '',
            ];
            $steps[3] = [
                'title' => "已完成",
                'active' => false,
                'description' => '',
            ];
        }

        switch ($aftersales->status) {
            case Aftersales::STATUS_IN_REVIEW: //审核处理中
                $current = 1;
                $steps[1]["active"] = true;
                $steps[1]["description"] = "售后审核中，请耐心等待";
                break;
            case Aftersales::STATUS_APPROVED_FOR_PROCESSING: //审核通过
                $current = 1;
                $steps[1]["active"] = true;
                $steps[1]["description"] = "售后审核通过，等待处理";
                break;
            case Aftersales::STATUS_REFUSE: //审核未通过
                $current = 1;
                $steps[1]["active"] = true;
                $steps[1]["description"] = "您的申请未能通过，详情请联系客服";
                break;
            case Aftersales::STATUS_SEND_BACK: //售后收货中
                $current = 2;
                $steps[1]["active"] = true;
                $steps[2]["active"] = true;
                $steps[2]["description"] = "售后处理中";
                break;
            case Aftersales::STATUS_RETURNED: //售后已处理
                $current = 2;
                $steps[1]["active"] = true;
                $steps[2]["active"] = true;
                $steps[2]["description"] = "申请已处理，等待您确认";
                break;
            case Aftersales::STATUS_COMPLETE:
                // 已完成
                if ($aftersales->type == Aftersales::AFTERSALES_TYPE_PAYRETURN) {
                    $current = 2;
                    $status = 'finish';
                    $steps[1]["active"] = true;
                    $steps[2]["active"] = true;
                    $steps[2]["description"] = "完成";
                    unset($steps[3]);
                } else {
                    $current = 3;
                    $status = 'finish';
                    $steps[1]["active"] = true;
                    $steps[2]["active"] = true;
                    $steps[3]["active"] = true;
                    $steps[3]["description"] = "完成";
                }
                break;
            case Aftersales::STATUS_CANCEL:
                // 已取消
                $current = 1;
                $status = 'finish';
                $steps[1]["title"] = "已取消";
                $steps[1]["active"] = true;
                $steps[1]["description"] = "";
                unset($steps[2]);
                unset($steps[3]);
                break;
        }
        return [
            'current' => $current,
            'status' => $status,
            'steps' => $steps,
        ];
    }

    /**
     * 获取名称
     *
     * @param int $id
     * @return string|null
     */
    public function getName(int $id): ?string
    {
        return Aftersales::with("items")->where('aftersale_id', $id)->find()->product_name;
    }

    /**
     * 同意或拒绝售后
     * @return bool
     */
    public function agreeOrRefuse(int $aftersales_id, array $data): bool
    {
        $aftersales = Aftersales::find($aftersales_id);
        if ($aftersales['status'] != Aftersales::STATUS_IN_REVIEW) {
            throw new ApiException(/** LANG */'该售后状态不能操作');
        }
        if (in_array($aftersales['status'], [2, 3])) {
            throw new ApiException(/** LANG */'售后状态错误');
        }
        if ($data['status'] == Aftersales::STATUS_REFUSE && empty($data['reply'])) {
            throw new ApiException(/** LANG */'拒接请填原因');
        }
        if ($data['status'] == Aftersales::STATUS_APPROVED_FOR_PROCESSING && $aftersales['aftersale_type'] == Aftersales::AFTERSALES_TYPE_RETURN && empty($data['return_address'])) {
            throw new ApiException(/** LANG */'同意退货退款请填写详细退货地址：姓名、联系方式、具体地址');
        }
        if ($data['status'] == Aftersales::STATUS_APPROVED_FOR_PROCESSING && $aftersales['aftersale_type'] == Aftersales::AFTERSALES_TYPE_RETURN) {
            //如果退货退款申请通过则跳到待用户寄回状态
            $data['status'] = Aftersales::STATUS_SEND_BACK;
        }
        try {
            Db::startTrans();
            $result = Aftersales::where('aftersale_id', $aftersales_id)->save($data);

            if ($aftersales['aftersale_type'] == Aftersales::AFTERSALES_TYPE_PAYRETURN) {
                // 创建退款申请
                $apply_data = [
                    "refund_type" => $aftersales->order_id ? 1 : 2,
                    "order_id" => $aftersales->order_id,
                    "user_id" => $aftersales->user_id,
                    "aftersale_id" => $aftersales_id,
                ];
                if (!app(RefundApplyService::class)->applyRefund($apply_data)) {
                    throw new \Exception('创建退款申请失败');
                }
            }
            Db::commit();
            $this->addAdminLog($aftersales_id);
            if ($result !== false) {
                $this->addAftersaleLog($aftersales_id, $data);
            }
        } catch (\Exception $exception) {
            Db::rollback();
            throw new ApiException($exception->getMessage());

        }
        return $result;
    }

    /**
     * 售后确认收到货接口
     * @return bool
     */
    public function receive(int $aftersales_id): bool
    {
        $aftersales = Aftersales::find($aftersales_id);
        if ($aftersales->status != Aftersales::STATUS_SEND_BACK) {
            throw new ApiException(/** LANG */'该售后状态不能操作');
        }
        $data = [];
        $data['status'] = Aftersales::STATUS_RETURNED;
        try {
            Db::startTrans();
            $result = Aftersales::where('aftersale_id', $aftersales_id)->save($data);
            // 创建退款申请
            $apply_data = [
                "refund_type" => $aftersales->order_id ? 1 : 2,
                "order_id" => $aftersales->order_id,
                "user_id" => $aftersales->user_id,
                "aftersale_id" => $aftersales_id,
            ];
            if (!app(RefundApplyService::class)->applyRefund($apply_data)) {
                throw new \Exception('创建退款申请失败');
            }
            $this->addAdminLog($aftersales_id);
            if ($result !== false) {
                $this->addAftersaleLog($aftersales_id, $data);
            }
            Db::commit();
        } catch (\Exception $exception) {
            Db::rollback();
            throw new ApiException($exception->getMessage());

        }
        return $result;
    }

    protected function addAdminLog($aftersales_id): bool
    {
        return AdminLog::add('更新退换货:' . $aftersales_id);
    }

    /**
     * 记录售后日志
     * @param $aftersales_id
     * @param $data
     * @return bool
     */
    protected function addAftersaleLog($aftersales_id, $data): bool
    {
        $reply = isset($data["reply"]) ? $data["reply"] : '';
        $log_info = Aftersales::STATUS_NAME[$data['status']] . ":" . $reply;
        $aftersales_log = [
            "aftersale_id" => $aftersales_id,
            "log_info" => $log_info,
        ];
        if (request()->userId) {

            $aftersales_log['user_name'] = User::where('user_id', request()->userId)->value("username");
        } else {
            $aftersales_log['admin_name'] = AdminUser::where('admin_id', request()->adminUid)->value("username");
        }
        // 生成售后记录
        AftersalesLog::create($aftersales_log);
        return true;
    }

    /**
     * 执行退换货添加或更新
     *
     * @param int $id
     * @param array $data
     * @param bool $isAdd
     * @return int|bool
     * @throws ApiException
     */
    public function updateAftersales(int $id, array $data, bool $isAdd = false)
    {
        validate(AftersalesValidate::class)->only(array_keys($data))->check($data);
        $data["number"] = $data["modify_number"];
        unset($data["modify_number"]);
        if ($isAdd) {
            unset($data['aftersale_id']);
            $result = Aftersales::create($data);
            AdminLog::add('新增退换货' . $this->getName($result->aftersale_id));
            $aftersale_id = $result->aftersale_id;
        } else {
            if (!$id) {
                throw new ApiException('#id错误');
            }
            $result = Aftersales::where('aftersale_id', $id)->save($data);
            AdminLog::add('更新退换货:' . $this->getName($id));
            $aftersale_id = $id;
        }
        if ($result !== false) {
            $admin_name = AdminUser::where('admin_id', request()->adminUid)->value("username");
            $log_info = Aftersales::STATUS_NAME[$data['status']] . ":" . $data["reply"];
            // 生成售后记录
            $aftersales_log = [
                "aftersale_id" => $aftersale_id,
                "log_info" => $log_info,
                "admin_name" => $admin_name,
            ];
            AftersalesLog::create($aftersales_log);
            return true;
        }
        return false;
    }

    /**
     * 更新单个字段
     *
     * @param int $id
     * @param array $data
     * @return int|bool
     * @throws ApiException
     */
    public function updateAftersalesField(int $id, array $data)
    {
        validate(AftersalesValidate::class)->only(array_keys($data))->check($data);
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = Aftersales::where('aftersale_id', $id)->save($data);
        AdminLog::add('更新退换货:' . $this->getName($id));
        return $result !== false;
    }

    /**
     * 删除退换货
     *
     * @param int $id
     * @return bool
     */
    public function deleteAftersales(int $id): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $get_name = $this->getName($id);
        $result = Aftersales::destroy($id);

        if ($result) {
            AdminLog::add('删除退换货:' . $get_name);
        }

        return $result !== false;
    }

    /**
     * 可售后的订单列表过滤 -- PC 端
     * @return Order
     */
    public function afterSalesOrderFilter()
    {
        $in_return_time = Time::now() - 3600 * 24 * 30 * 12; //默认12个月内可退换货
        $query = Order::with([
            "items" => function ($query) {
                $query->field("item_id,order_id,product_id,price,quantity,product_name,product_sn,pic_thumb");
            },
        ])
            ->field("order_id,order_sn,shipping_time")
            ->where(["is_del" => 0, "user_id" => request()->userId])
            ->whereIn("order_status", [1, 2, 5]);
        return $query;
    }

    /**
     * 可售后的订单列表
     * @param array $filter
     * @return object|Order[]|array|\think\Collection
     */
    public function afterSalesOrderList(array $filter): object
    {
        $query = $this->afterSalesOrderFilter();
        $list = $query->page($filter['page'], $filter['size'])->order($filter["sort_field"],
            $filter["sort_order"])->select();

        // 获取订单商品当前的售后信息
        $list = $list->each(function ($order) {
            foreach ($order->items as $item) {
                // 获取可申请的数量
                $sumValidNum = AftersalesItem::hasWhere(
                    "aftersales", function ($query) {
                        $query->whereIn('status', Aftersales::VALID_STATUS);
                    }
                )->where('order_item_id', $item->item_id)->sum('number') ?? 0;
                $can_apply_quantity = $item->quantity - (int) $sumValidNum;

                $item->aftersales_item = AftersalesItem::with(["aftersales" => function ($query) use ($item) {
                    $query->where("order_id", $item->order_id);
                }])
                    ->where("order_item_id", $item->item_id)->order("aftersales_item_id", "desc")->findOrEmpty()->toArray();

                if (!empty($item->aftersales_item)) {
                    if ($item->aftersales_item["status"] == Aftersales::STATUS_COMPLETE) {
                        // 售后已完成
                        if ($can_apply_quantity == 0) {
                            // 不可申请--跳转详情
                            $item->aftersale_flag = 0;
                        } else {
                            // 可申请
                            $item->aftersale_flag = 1;
                        }
                    } elseif ($item->aftersales_item["status"] == Aftersales::STATUS_CANCEL) {
                        // 售后取消 -- 可申请
                        $item->aftersale_flag = 1;
                    } else {
                        $item->aftersale_flag = 0;
                    }
                } else {
                    $item->aftersale_flag = 1;
                }
            }
        });
        return $list;
    }

    /**
     * 售后申请详情
     * @param int $id
     */
    public function getAfterSalesDetail(int $order_id, int $item_id): object
    {
        $model = OrderItem::where('order_id', $order_id)->field("item_id,pic_thumb,product_sn,product_name,price,quantity,(price * quantity) as subtotal,sku_data");

        if (!empty($item_id)) {
            $model = $model->where("item_id", $item_id);
        }
        $list = $model->select();

        if (empty($list)) {
            throw new ApiException(/** LANG */'该商品订单不存在');
        }

        foreach ($list as $k => &$v) {
            $sumValidNum = AftersalesItem::withJoin([
                "aftersales",
            ])->whereIn('aftersales.status', Aftersales::VALID_STATUS)->where('order_item_id',
                $v["item_id"])->sum('number');
            $v->can_apply_quantity = $v->quantity - (int) $sumValidNum;
        }
        return $list;
    }

    /**
     * 售后申请
     * @param array $data
     * @return mixed
     */
    public function afterSalesApply(array $data)
    {
        $data["status"] = 1;
        $data['aftersales_sn'] = date("YmdHis") . rand(1000, 99999);
        $aftersale_items = $data['items'];
        $order = Order::where('order_id', $data['order_id'])->find();
        if (!$order->hasPay()) {
            throw new ApiException(/** LANG */'未支付订单不允许售后');
        }
        foreach ($aftersale_items as $k => $item) {
            if (empty($item['number'])) {
                unset($aftersale_items[$k]);
                continue;
            }
            if (AftersalesItem::withJoin([
                "aftersales",
            ])->whereIn('aftersales.status', Aftersales::PROGRESSING_STATUS)->where('aftersales.order_id',
                $data['order_id'])->where('order_item_id', $item["order_item_id"])->count()) {
                throw new ApiException(/** LANG */'该商品已申请售后,请勿重复申请');
            }
            $orderQuantity = OrderItem::where('order_id', $data['order_id'])->where('item_id',
                $item["order_item_id"])->value('quantity');
            $sumValidNum = AftersalesItem::withJoin([
                "aftersales",
            ])->whereIn('aftersales.status', Aftersales::VALID_STATUS)->where('order_item_id',
                $item["order_item_id"])->sum('number');
            if ($orderQuantity < $sumValidNum + $item['number']) {
                throw new ApiException(/** LANG */'该商品可申请数量不足');
            }
        }

        if (empty($aftersale_items)) {
            throw new ApiException(/** LANG */'请填写申请售后数量');
        }
        unset($data['items']);
        Db::startTrans();
        try {
            $data['user_id'] = request()->userId;
            $result = Aftersales::create($data);
            foreach ($aftersale_items as $item) {
                $result->aftersalesItems()->save($item);
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw new ApiException($e->getMessage());
        }

        $aftersale_id = $result->aftersale_id;
        //记录日志
        if ($result !== false) {
            $username = User::where('user_id', request()->userId)->value("username");
            $log_info = "会员发起了" . Aftersales::AFTERSALES_TYPE_NAME[$data['aftersale_type']] . "：" . $data["description"];
            // 生成售后记录
            $aftersales_log = [
                "aftersale_id" => $aftersale_id,
                "log_info" => $log_info,
                "user_name" => $username,
            ];
            AftersalesLog::create($aftersales_log);
        }
        return $aftersale_id;
    }

    /**
     * 售后记录
     * @param array $filter
     * @return array
     */
    public function afterSalesRecord(array $filter, int $user_id): array
    {
        $query = Aftersales::with(['orderSn', "aftersales_items.items"])->append([
            'aftersales_type_name',
            "status_name",
        ])
            ->where("user_id", $user_id);
        $count = $query->count();
        $list = $query->page($filter['page'], $filter['size'])->order($filter["sort_field"],
            $filter["sort_order"])->select();
        return [
            "count" => $count,
            "list" => $list,
        ];
    }

    /**
     * 提交售后反馈记录
     * @param int $id
     * @param array $data
     * @return mixed
     * @throws ApiException
     */
    public function submitFeedbackRecord(int $id, array $data, int $user_id): mixed
    {
        $aftersales = Aftersales::findOrEmpty($id);
        if (empty($aftersales)) {
            throw new ApiException(/** LANG */'该售后申请不存在');
        }
        $aftersales_log = [
            "aftersale_id" => $aftersales->aftersale_id,
            "log_info" => $data["log_info"],
            "user_name" => !empty(request()->userId) ? User::findOrEmpty($user_id)->username ?? "" : "",
            "return_pic" => isset($data["return_pic"]) ? $data["return_pic"] : "",
            'admin_name' => !empty(request()->adminUid) ? AdminUser::findOrEmpty(request()->adminUid)->username ?? "" : "",
        ];

        if ($aftersales->status == Aftersales::STATUS_SEND_BACK && !empty(request()->userId)) {
            //要求寄回
            $aftersales_data = [
                "tracking_no" => $data["tracking_no"],
                "logistics_name" => $data["logistics_name"],
            ];
            $aftersales_data['status'] = Aftersales::STATUS_RETURNED;
            $aftersales->save($aftersales_data);
        }
        $result = AftersalesLog::create($aftersales_log);
        return $result->log_id;
    }

}
