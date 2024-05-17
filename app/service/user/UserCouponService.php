<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 优惠券
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\user;

use app\common\exceptions\ApiException;
use app\common\utils\Time;
use app\model\user\UserCoupon;
use app\service\BaseService;

/**
 * 用户优惠券服务类
 */
class UserCouponService extends BaseService
{
    /**
     * 获取筛选结果
     *
     * @param array $filter
     * @return array
     */
    public function getFilterResult(array $filter)
    {
        $query = $this->filterQuery($filter)->with(['coupon'])->append(["status_name", "status"]);
        $count = $query->count();
        $list = $query->select()->toArray();
        // 根据状态排序
        array_multisort(array_column($list, 'status'), SORT_ASC, $list);
        //分页
        $result = array_slice($list, (($filter["page"] ?? 1) - 1) * ($filter["size"] ?? 9), ($filter["size"] ?? 9));
        return [
            'count' => $count,
            'list' => $result,
        ];
    }

    /**
     * 筛选查询
     *
     * @param array $filter
     * @return object
     */
    public function filterQuery(array $filter): object
    {
        $query = UserCoupon::query();

        if (isset($filter["sort_field"], $filter["sort_order"]) && !empty($filter["sort_field"]) && !empty($filter["sort_order"])) {
            $query->order($filter['sort_field'], $filter['sort_order']);
        }
        if (isset($filter['used_time'])) {
            $query->where('used_time', $filter['used_time']);
        }
        if (isset($filter['start_date'])) {
            $query->where('start_date', '<=', $filter['start_date']);
        }
        if (isset($filter['end_date'])) {
            $query->where('end_date', '>=', $filter['end_date']);
        }
        if (isset($filter['user_id'])) {
            $query->where('user_id', $filter['user_id']);
        }
        return $query;
    }

    /**
     * 删除优惠券
     * @param int $id
     * @return bool
     * @throws ApiException
     */
    public function deleteUserCoupon(int $id): bool
    {
        if (!$id) {
            throw new ApiException('#id错误');
        }
        $result = UserCoupon::destroy($id);

        return $result !== false;
    }

    /**
     * 使用优惠券
     * @param array $user_coupon_ids
     * @param int $user_id
     * @param int $order_id
     * @return bool
     */
    public function useCoupon(array $user_coupon_ids, int $user_id, int $order_id): bool
    {
        return UserCoupon::whereIn('id', $user_coupon_ids)->where('user_id', $user_id)->save(['used_time' => Time::now(), 'order_id' => $order_id]);
    }

    /**
     * 返回优惠券
     * @param int $user_id
     * @param int $order_id
     * @return bool
     */
    public function returnUserCoupon(int $user_id, int $order_id): bool
    {
        return UserCoupon::where('user_id', $user_id)->where('order_id', $order_id)->save(['used_time' => 0, 'order_id' => 0]);
    }

    /**
     * 检测用户是否领取了该优惠券
     * @param int $coupon_id
     * @param int $user_id
     * @return int
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function checkUserHasNormalCoupon(int $coupon_id, int $user_id): int
    {
        $time = Time::now();
        $where = [
            ['coupon_id', '=', $coupon_id],
            ['user_id', '=', $user_id],
            ['order_id', '=', 0],
            ['start_date', '<=', $time],
            ['end_date', '>=', $time],
        ];
        $result = UserCoupon::where($where)->find();
        if ($result) return 1;
        return 0;
    }

    /**
     * 获取用户可使用优惠券数量
     * @param int $user_id
     * @return int
     * @throws \think\db\exception\DbException
     */
    public function getUserNormalCouponCount(int $user_id): int
    {
        $time = Time::now();
        $where = [
            ['user_id', '=', $user_id],
            ['order_id', '=', 0],
            ['start_date', '<=', $time],
            ['end_date', '>=', $time],
        ];
        return UserCoupon::where($where)->count();
    }
}
