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

namespace app\service\api\admin\promotion;

use app\model\promotion\Coupon;
use app\model\user\User;
use app\model\user\UserCoupon;
use app\service\api\admin\product\CategoryService;
use app\service\api\admin\user\UserCouponService;
use app\service\api\admin\user\UserService;
use app\service\core\BaseService;
use exceptions\ApiException;
use utils\Time;

/**
 * 优惠券服务类
 */
class CouponService extends BaseService
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
        $filter['page'] = $filter['page'] ?? 1;
        $query = $this->filterQuery($filter);
        if (isset($filter["receive_flag"]) && $filter["receive_flag"] == 1) {
            $list = $query->append(["is_receive"])->select()->toArray();
            // 根据领取状态排序
            array_multisort(array_column($list, 'is_receive'), SORT_ASC, $list);
            $result = array_slice($list, (($filter["page"] ?? 1) - 1) * ($filter["size"] ?? 15), ($filter["size"] ?? 15));
        } else {
            $result = $query->order('coupon_id', 'desc')->page($filter['page'], $filter['size'])->select()->toArray();
        }

        return $result;
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
        $query = Coupon::query();
        // 处理筛选条件
        $query->where('is_delete', 0);
        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->where('coupon_name', 'like', '%' . $filter['keyword'] . '%');
        }

        if (isset($filter['sort_field'], $filter['sort_order']) && !empty($filter['sort_field']) && !empty($filter['sort_order'])) {
            $query->order($filter['sort_field'], $filter['sort_order']);
        }
        if (isset($filter['is_show']) && $filter['is_show'] != -1) {
            $query->where('is_show', $filter['is_show']);
        }
        // 有效限内
        if (isset($filter['valid_date']) && $filter['valid_date'] === 1) {
            $query->where('use_start_date', '<=', Time::now());
            $query->where('use_end_date', '>=', Time::now());
        }

        // 可领取时间内
        if (isset($filter["receive_date"]) && $filter["receive_date"] === 1) {
            $query->where('send_start_date', '<=', Time::now());
            $query->where('send_end_date', '>=', Time::now());
        }

        //shop_id
        if (isset($filter["shop_id"])) {
            $query->where('shop_id', '=', $filter["shop_id"]);
        }
        return $query;
    }

    /**
     * 获取详情
     *
     * @param int $id
     * @return Coupon | null
     */
    public function getDetail(int $id, int $user_id = 0): Coupon | null
    {
        $result = Coupon::where('coupon_id', $id)->find();
        if ($result) {
            if ($user_id > 0) {
                $result['is_receive'] = app(UserCouponService::class)->checkUserHasNormalCoupon($id, $user_id) ? 1 : 0;
            } else {
                $result['is_receive'] = 0;
            }
        }
        return $result;
    }

    /**
     * 数据判断
     * @param array $data
     * @return array
     */
    public function dataJudge(array $data): array
    {
        if (!empty($data['use_start_date']) && !empty($data['use_end_date'])) {
            // 使用日期
            $data['use_start_date'] = Time::toTime($data['use_start_date']);
            $data['use_end_date'] = Time::toTime($data['use_end_date']);
        }

        if (!empty($data['send_start_date']) && !empty($data['send_end_date'])) {
            // 发放日期
            $data["send_start_date"] = Time::toTime($data['send_start_date']);
            $data["send_end_date"] = Time::toTime($data['send_end_date']);
        }
        return $data;
    }

    /**
     * 添加优惠券
     * @param array $data
     * @return bool
     */
    public function createCoupon(array $data): bool
    {
        $data = $this->dataJudge($data);
        $result = Coupon::create($data);
        return $result !== false;
    }

    /**
     * 执行优惠券更新
     * @param int $id
     * @param array $data
     * @return bool
     * @throws ApiException
     */
    public function updateCoupon(int $id, array $data): bool
    {
        $data = $this->dataJudge($data);
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = Coupon::where('coupon_id', $id)->save($data);
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
    public function updateCouponField(int $id, array $data)
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = Coupon::where('coupon_id', $id)->save($data);
        return $result !== false;
    }

    /**
     * 删除优惠券
     *
     * @param int $id
     * @return bool
     */
    public function deleteCoupon(int $id): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = Coupon::where('coupon_id', $id)->save(['is_delete' => 1]);
        return $result !== false;
    }

    /**
     * PC 领取优惠券
     * @param int $coupon_id
     * @param int $user_id
     * @return bool
     * @throws ApiException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function claimCoupons(int $coupon_id, int $user_id): bool
    {
        if (app(UserCouponService::class)->checkUserHasNormalCoupon($coupon_id, $user_id)) {
            throw new ApiException(/** LANG */'优惠券已领取');
        }
        $coupon = Coupon::find($coupon_id);
        if (empty($coupon)) {
            throw new ApiException(/** LANG */'优惠券不存在');
        }
        $coupon_data = [
            "coupon_id" => $coupon_id,
            "user_id" => $user_id,
            "start_date" => !empty($coupon->use_start_date) ? Time::toTime($coupon->use_start_date) : 0,
            "end_date" => !empty($coupon->use_end_date) ? Time::toTime($coupon->use_end_date) : 0,
        ];
        $result = UserCoupon::create($coupon_data);
        return $result !== false;
    }

    /**
     * 获得商品优惠券
     * @return Coupon|array
     */
    public function getProductCouponList(
        int $product_id,
        int $shop_id,
        int $brand_id,
        int $user_id,
        int $category_id = 0
    ): Coupon|array
    {
        $coupon = Coupon::where('use_start_date', '<', time())->where('use_end_date', '>', time() - 86400);
        if ($shop_id) {
            $coupon->where(function ($query) use ($shop_id) {
                $query->where('shop_id', 0)->whereOr('shop_id', $shop_id);
            });
        }
        $coupon->where('is_show', 1);
        $coupon->where('is_delete', 0);
        $coupon = $coupon->select()->toArray();
        foreach ($coupon as $key => $c) {
            $send_range_data = $c['send_range_data'];
            //不为全部商品时判断
            if ($c['send_range'] != 0) {
                //指定分类
                if ($c['send_range'] == 1) {
                    if (empty($c['send_range_data']) || empty($category_id)) {
                        continue;
                    }

                    $allParentIds = [];
                    $parentCategory = app(CategoryService::class)->getParentCategory($category_id);
                    if (!empty($parentCategory['category_ids'])) {
                        $allParentIds = $parentCategory['category_ids'];
                    }
                    $allParentIds[] = $category_id;
                    if (!empty($allParentIds)) {
                        foreach ($allParentIds as $cid) {
                            if ($send_range_data && is_array($send_range_data) && in_array($cid, $send_range_data)) {
                                unset($coupon[$key]);
                            }
                        }
                    }
                    //指定品牌
                } elseif ($c['send_range'] == 2) {
                    if (is_array($send_range_data) && !in_array($brand_id, $send_range_data)) {
                        unset($coupon[$key]);
                    }
                    //指定商品
                } elseif ($c['send_range'] == 3) {
                    if (is_array($send_range_data) && !in_array($product_id, $send_range_data)) {
                        unset($coupon[$key]);
                    }
                    //不包含指定商品
                } elseif ($c['send_range'] == 4) {
                    if (is_array($send_range_data) && in_array($product_id, $send_range_data)) {
                        unset($coupon[$key]);
                    }
                }
            }
            //新人专享优惠券
            if ($c['is_new_user'] && $user_id) {
                if (!app(UserService::class)->isNew($user_id)) {
                    unset($coupon[$key]);
                }
            }
            //仅限会员等级
            if ($c['limit_user_rank'] && $user_id) {
                $limit_user_rank = $c['limit_user_rank'];
                if (!empty($limit_user_rank) && is_array($limit_user_rank)) {
                    $user = User::find($user_id);
                    if (!in_array($user['rank_id'], $limit_user_rank)) {
                        unset($coupon[$key]);
                    }
                }
            }
        }
        return array_values($coupon);

    }

}
