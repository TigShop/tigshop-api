<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 优惠券
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\adminapi\controller\promotion;

use app\adminapi\AdminBaseController;
use app\service\api\admin\promotion\CouponService;
use app\service\api\admin\user\UserRankService;
use app\validate\promotion\CouponValidate;
use exceptions\ApiException;
use think\App;
use think\exception\ValidateException;
use think\facade\Db;
use think\Response;

/**
 * 优惠券控制器
 */
class Coupon extends AdminBaseController
{
    protected CouponService $couponService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param CouponService $couponService
     */
    public function __construct(App $app, CouponService $couponService)
    {
        parent::__construct($app);
        $this->couponService = $couponService;
        $this->checkAuthor('couponManage'); //权限检查
    }

    /**
     * 列表页面
     *
     * @return Response
     */
    public function list(): Response
    {
        $filter = $this->request->only([
            'keyword' => '',
            'page/d' => 1,
            'size/d' => 15,
            'sort_field' => 'coupon_id',
            'sort_order' => 'desc',
        ], 'get');
        $filter['shop_id'] = request()->shopId;

        $filterResult = $this->couponService->getFilterResult($filter);
        $total = $this->couponService->getFilterCount($filter);

        return $this->success([
            'filter_result' => $filterResult,
            'filter' => $filter,
            'total' => $total,
        ]);
    }

    /**
     * 配置型
     * @return Response
     */
    public function config(): Response
    {
        // 会员等级
        $rank_list = app(UserRankService::class)->getUserRankList();
        return $this->success([
            'rank_list' => $rank_list,
        ]);
    }

    /**
     * 详情
     * @return Response
     */
    public function detail(): Response
    {
        $id = input('id/d', 0);
        $item = $this->couponService->getDetail($id);
        if ($item->shop_id != request()->shopId) {
            throw new ApiException(/** LANG */'优惠券不存在');
        }

        return $this->success([
            'item' => $item,
        ]);
    }

    /**
     * 请求数据
     * @return array
     */
    public function requestData(): array
    {
        $data = $this->request->only([
            'coupon_name' => '',
            'coupon_desc' => '',
            'coupon_money' => '',
            'min_order_amount' => '',
            'send_range' => '',
            'send_range_data' => "",
            'is_global' => '',
            'is_new_user' => '',
            'is_show' => '',
            'coupon_discount' => '',
            'coupon_type' => '',
            'enabled_click_get' => '',
            'limit_user_rank' => '',
            'send_start_date' => "",
            "send_end_date" => "",
            "use_start_date" => "",
            "use_end_date" => "",
        ], 'post');

        if ($data['coupon_type'] == 1) {
            unset($data['coupon_discount']);
        }
        return $data;
    }

    /**
     * 添加
     * @return Response
     * @throws \exceptions\ApiException
     */
    public function create()
    {
        $data = $this->requestData();

        try {
            validate(CouponValidate::class)
                ->scene('create')
                ->check($data);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }
        $data['shop_id'] = request()->shopId;

        $result = $this->couponService->createCoupon($data);
        if ($result) {
            return $this->success(/** LANG */'优惠券添加成功');
        } else {
            return $this->error(/** LANG */'优惠券添加失败');
        }
    }

    /**
     * 执行更新操作
     *
     * @return Response
     */
    public function update(): Response
    {
        $id = input('coupon_id/d', 0);

        $item = $this->couponService->getDetail($id);
        if ($item->shop_id != request()->shopId) {
            throw new ApiException(/** LANG */'优惠券不存在');
        }

        $data = $this->requestData();
        $data["coupon_id"] = $id;
        try {
            validate(CouponValidate::class)
                ->scene('update')
                ->check($data);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        $result = $this->couponService->updateCoupon($id, $data);
        if ($result) {
            return $this->success(/** LANG */'优惠券更新成功');
        } else {
            return $this->error(/** LANG */'优惠券更新失败');
        }
    }

    /**
     * 更新单个字段
     *
     * @return Response
     */
    public function updateField(): Response
    {
        $id = input('id/d', 0);
        $field = input('field', '');

        $item = $this->couponService->getDetail($id);
        if ($item->shop_id != request()->shopId) {
            throw new ApiException(/** LANG */'优惠券不存在');
        }

        if (!in_array($field, ['coupon_name', 'sort_order', 'is_show', 'coupon_discount', 'min_order_amount'])) {
            return $this->error(/** LANG */'#field 错误');
        }

        $data = [
            'coupon_id' => $id,
            $field => input('val'),
        ];

        $this->couponService->updateCouponField($id, $data);

        return $this->success(/** LANG */'更新成功');
    }

    /**
     * 删除
     *
     * @return Response
     */
    public function del(): Response
    {
        $id = input('id/d', 0);
        $item = $this->couponService->getDetail($id);
        if (!$item) {
            throw new ApiException(/** LANG */'优惠券不存在');
        }
        $this->couponService->deleteCoupon($id);
        return $this->success(/** LANG */'指定项目已删除');
    }

    /**
     * 批量操作
     *
     * @return Response
     */
    public function batch(): Response
    {
        if (empty(input('ids')) || !is_array(input('ids'))) {
            return $this->error(/** LANG */'未选择项目');
        }

        if (input('type') == 'del') {
            try {
                //批量操作一定要事务
                Db::startTrans();
                foreach (input('ids') as $key => $id) {
                    $id = intval($id);
                    $this->couponService->deleteCoupon($id);
                }
                Db::commit();
            } catch (\Exception $exception) {
                Db::rollback();
                throw new ApiException($exception->getMessage());
            }

            return $this->success(/** LANG */'批量操作执行成功！');
        } else {
            return $this->error(/** LANG */'#type 错误');
        }
    }
}
