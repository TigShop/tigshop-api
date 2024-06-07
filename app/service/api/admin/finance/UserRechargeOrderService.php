<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 充值申请
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\finance;

use app\model\finance\UserRechargeOrder;
use app\model\finance\UserWithdrawApply;
use app\model\promotion\RechargeSetting;
use app\service\core\BaseService;
use exceptions\ApiException;
use utils\Time;

/**
 * 充值申请服务类
 */
class UserRechargeOrderService extends BaseService
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
        $query = $this->filterQuery($filter)->with(["user"])->append(["status_type"]);
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
        $query = UserRechargeOrder::query();
        // 处理筛选条件

        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->username($filter["keyword"]);
        }

        if (isset($filter['sort_field'], $filter['sort_order']) && !empty($filter['sort_field']) && !empty($filter['sort_order'])) {
            $query->order($filter['sort_field'], $filter['sort_order']);
        }

        if (isset($filter["status"]) && $filter["status"] != -1) {
            $query->where('status', $filter["status"]);
        }

        // 支付时间
        if (isset($filter['pay_time']) && !empty($filter['pay_time'])) {
            $filter['pay_time'] = is_array($filter['pay_time']) ? $filter['pay_time'] : explode(',', $filter['pay_time']);
            list($start_date, $end_date) = $filter['pay_time'];
            $start_date = Time::toTime($start_date);
            $end_date = Time::toTime($end_date) + 86400;
            $query->whereTime('paid_time', 'between', [$start_date, $end_date]);
        }

        if(isset($filter["user_id"]) && $filter["user_id"] > 0){
            $query->where('user_id', $filter["user_id"]);
        }
        return $query;
    }

    /**
     * 获取详情
     *
     * @param int $id
     * @return UserRechargeOrder
     * @throws ApiException
     */
    public function getDetail(int $id): UserRechargeOrder
    {
        $result = UserRechargeOrder::where('order_id', $id)->append(["status_type"])->find();

        if (!$result) {
            throw new ApiException(/** LANG */'充值申请不存在');
        }

        return $result;
    }

    /**
     * 获取名称
     *
     * @param int $id
     * @return string|null
     */
    public function getName(int $id): ?string
    {
        return UserRechargeOrder::where('order_id', $id)->value('postscript');
    }

    /**
     * 更新会员的可用余额
     * @param array $data
     * @return void
     */
    public function accountChange(array $data): void
    {
        // 处理状态已完成
        if ($data["status"] == 1) {
            $user_balance_log = [
                "balance" => $data["amount"],
                "change_desc" => $data["postscript"],
                "change_type" => 1,
            ];
            // 更新会员的可用余额
            app(UserBalanceLogService::class)->accountChange($data["user_id"], $user_balance_log);
        }
    }

    /**
     * 添加充值申请
     * @param array $data
     * @return bool
     */
    public function createUserRechargeOrder(array $data): bool
    {
        if ($data["status"] == 1) {
            $data["paid_time"] = Time::now();
        }
        $result = UserRechargeOrder::create($data);
        if ($result !== false) {
            $this->accountChange($data);
            return true;
        }
        return false;
    }

    /**
     * 执行充值申请更新
     *
     * @param int $id
     * @param array $data
     * @param bool $isAdd
     * @return int|bool
     * @throws ApiException
     */
    public function updateUserRechargeOrder(int $id, array $data)
    {
        if ($data["status"] == 1) {
            $data["paid_time"] = Time::now();
        }
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        if (UserRechargeOrder::find($id)->status == 1) {
            throw new ApiException(/** LANG */'该笔充值申请已支付，不能修改');
        }

        $result = UserRechargeOrder::where('order_id', $id)->save($data);
        if ($result !== false) {
            // 处理状态已完成
            $this->accountChange($data);
            return true;
        }
        return false;
    }

    /**
     * 删除充值申请
     *
     * @param int $id
     * @return bool
     */
    public function deleteUserRechargeOrder(int $id): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = UserRechargeOrder::destroy($id);
        return $result !== false;
    }

    /**
     * 充值用户数量
     * @param array $data
     * @return mixed
     */
    public function getRechargeUserTotal(array $data)
    {
        return UserRechargeOrder::paidTime($data)->paid()->group("user_id")->count();
    }

    /**
     * 充值申请
     * @param int $id
     * @param float $amount
     * @param int $user_id
     * @return int
     * @throws ApiException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function rechargeOperation(int $id, float $amount, int $user_id): int
    {
        if (!empty($amount)) {
            $discount_money = RechargeSetting::where('money', '<=', $amount)->order('money', 'DESC')->value('discount_money');
            $discount_money = empty($discount_money) ? 0 : floatval($discount_money);
        } else {
            $recharge = RechargeSetting::field("money,discount_money")->where("is_show", 1)->find($id);
            if (empty($recharge)) {
                throw new ApiException(/** LANG */"充值金额错误");
            }
            $amount = $recharge->money;
            $discount_money = $recharge->discount_money;
        }
        $user_recharge_order = [
            "user_id" => $user_id,
            "amount" => $amount,
            "discount_money" => $discount_money,
        ];
        $result = UserRechargeOrder::create($user_recharge_order);

        return $result->order_id;
    }

    /**
     * 申请记录列表
     * @param array $filter
     * @param int $user_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAccountDetails(array $filter, int $user_id): array
    {
        // 提现信息
        $withdraw_query = UserWithdrawApply::field("amount,add_time,postscript,status")->where("user_id", $user_id);
        // 充值信息
        $recharge_query = UserRechargeOrder::field("amount,add_time,postscript,status")->where("user_id", $user_id);

        if (isset($filter["status"]) && $filter["status"] != -1) {
            $withdraw_query->where("status", $filter["status"]);
            $recharge_query->where("status", $filter["status"]);
        }

        $withdraw_apply = $withdraw_query->append(["status_type"])->select();
        foreach ($withdraw_apply as $value) {
            $value->type = "提现";
        }
        $recharge_order = $recharge_query->append(["status_type"])->select();
        foreach ($recharge_order as $value) {
            $value->type = "充值";
        }
        $data = array_merge($withdraw_apply->toArray(), $recharge_order->toArray());

        // 二维数组排序
        array_multisort(array_column($data, $filter["sort_field"] ?? "add_time"), SORT_DESC, $data);
        $count = count($data);
        // 分页
        $data = array_slice($data, (($filter["page"] ?? 1) - 1) * ($filter["size"] ?? 15), ($filter["size"] ?? 15));
        return [
            "count" => $count,
            "list" => $data,
        ];
    }

    /**
     * 充值金额列表
     * @param array $filter
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getSettingList(array $filter): array
    {
        $query = RechargeSetting::where("is_show", 1);
        if (isset($filter["sort_field"], $filter["sort_order"]) && !empty($filter["sort_field"]) && !empty($filter["sort_order"])) {
            $query->order($filter["sort_field"], $filter["sort_order"]);
        }
        $count = $query->count();
        $result = $query->select();
        return [
            "count" => $count,
            "list" => $result,
        ];
    }

    /**
     * 设置充值订单已支付
     * @param int $order_id
     * @return void
     * @throws ApiException
     */
    public function setRechargePaid(int $order_id): void
    {
        $order = $this->getDetail($order_id);
        if ($order['status']) {
            throw new ApiException(/** LANG */'充值订单状态错误');
        }
        try {
            $update_data = [
                'status' => 1,
                'paid_time' => Time::now(),
            ];
            /* 更新会员预付款的到款状态 */
            UserRechargeOrder::where('order_id', $order_id)->save($update_data);
            /* 修改会员帐户金额 */
            $user_balance_log = [
                "balance" => $order["amount"] + $order['discount_money'],
                "change_desc" => $order["postscript"],
                "change_type" => 1,
            ];
            app(UserBalanceLogService::class)->accountChange($order['user_id'], $user_balance_log);
        } catch (\Exception $exception) {
            throw new ApiException($exception->getMessage());
        }
    }

}
