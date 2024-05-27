<?php
//**---------------------------------------------------------------------+
//** 通用接口控制器文件 -- 商品
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\index\controller\product;

use app\index\IndexBaseController;
use app\service\api\admin\order\CartService;
use app\service\api\admin\product\ProductDetailService;
use app\service\api\admin\product\ProductService;
use app\service\api\admin\promotion\CouponService;
use app\service\api\admin\user\FeedbackService;
use app\service\api\admin\user\UserCouponService;
use app\service\api\admin\user\UserService;
use exceptions\ApiException;
use think\App;
use think\Response;

/**
 * 商品控制器
 */
class Product extends IndexBaseController
{
    /**
     * 构造函数
     *
     * @param App $app
     */
    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    /**
     * 商品信息
     * @return Response
     * @throws ApiException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function detail(): \think\Response
    {
        // 此处id有可能传的是sn
        $product_id = input('id', 0);
        $sku_id = input('sku_id', 0);
        $goods_sn = input('sn', '');
        if (!empty($goods_sn)) {
            [$product_id, $sku_id] = app(ProductService::class)->getProductKeyBySn($goods_sn);
        }
        $productDetailService = new ProductDetailService($product_id);
        $product = $productDetailService->getDetail();
        return $this->success([
            'item' => $product,
            'desc_arr' => $productDetailService->getDescArr(),
            'sku_list' => $productDetailService->getSkuList(),
            'pic_list' => $productDetailService->getPicList(),
            'attr_list' => $productDetailService->getAttrList(),
            'rank_detail' => $productDetailService->getProductCommentRankDetail(),
            'seckill_detail' => $productDetailService->getSeckillInfo(),
            'service_list' => $productDetailService->getServiceList(),
            'checked_value' => $productDetailService->getSelectValue($sku_id),
            'consultation_total' => $productDetailService->getConsultationCount(),
        ]);
    }

    public function getComment(): \think\Response
    {
        $id = input('id/d', 0);
        $productDetailService = new ProductDetailService($id);
        return $this->success([
            'item' => $productDetailService->getProductCommentDetail(),
        ]);
    }

    public function getCommentList(): \think\Response
    {
        $id = input('id/d', 0);
        $filter = $this->request->only([
            'id' => $id,
            'type/d' => 1,
            'page/d' => 1,
        ], 'get');

        $productDetailService = new ProductDetailService($id);
        return $this->success([
            'filter_result' => $productDetailService->getProductCommentList($filter),
            'filter' => $filter,
            'total' => $productDetailService->getProductCommentCount($filter),
            'item' => $productDetailService->getProductCommentList($filter),
        ]);
    }

    /**
     * 获取商品咨询列表
     * @return \think\Response
     */
    public function getFeedbackList(): \think\Response
    {
        $filter = $this->request->only([
            'page/d' => 1,
            'size/d' => 15,
            'product_id/d' => 0,
            'sort_field' => 'id',
            'sort_order' => 'desc',
        ], 'get');
        if (empty($filter['product_id'])) {
            return $this->error('请选择商品');
        }
        $result = app(FeedbackService::class)->orderInquiryList($filter);
        return $this->success([
            'filter_result' => $result["list"],
            'filter' => $filter,
            'total' => $result["count"],
        ]);
    }

    public function getProductAvailability(): \think\Response
    {
        $id = input('id/d', 0);
        $sku_id = input('sku_id/d', 0);
        $productDetailService = new ProductDetailService($id);
        $result = $productDetailService->getProductSkuDetail($sku_id);
        if (request()->userId > 0) {
            //add user History
            try {
                app(UserService::class)->addProductHistory(request()->userId, $id);
            } catch (\Exception $exception) {

            }
        }
        return $this->success([
            'price' => $result['price'],
            'stock' => $result['stock'],
            'is_seckill' => $result['is_seckill'],
            'seckill_end_time' => $result['seckill_end_time'],
        ]);
    }

    public function addToCart(): \think\Response
    {
        $this->checkLogin();
        $id = input('id/d', 0);
        $number = input('number/d', 0);
        $sku_id = input('sku_id/d', 0);
        $is_quick = input('is_quick/d', 0) == 1 ? true : false;
        $result = app(CartService::class)->addToCart($id, $number, $sku_id, $is_quick);
        return $this->success([
            "item" => $result,
        ]);
    }

    /**
     * 商品列表
     * @return \think\Response
     */
    public function list(): \think\Response
    {
        $filter = $this->request->only([
            'is_show/d' => -1,
            'page/d' => 1,
            'size/d' => 15,
            'sort_field' => 'product_id',
            'sort_order' => 'desc',
            'product_id/d' => 0,
            'is_delete/d' => -1,
            'category_id/d' => 0,
            'brand_id/d' => 0,
            'ids' => null,
            'shop_id/d' => -2, // 店铺id
            'intro_type' => '', // 商品类型
            'coupon_id' => 0
        ], 'get');
        $filterResult = app(ProductService::class)->getFilterResult($filter);
        $total = app(ProductService::class)->getFilterCount($filter);
        $waiting_checked_count = app(ProductService::class)->getWaitingCheckedCount();

        return $this->success([
            'filter_result' => $filterResult,
            'filter' => $filter,
            'total' => $total,
            'waiting_checked_count' => $waiting_checked_count,
        ]);
    }

    /**
     * 商品优惠劵
     * @return \think\Response
     */
    public function getCouponList(): \think\Response
    {
        $filter = $this->request->only([
            'id/d' => 0,
        ], 'get');
        $product = app(ProductService::class)->getDetail($filter['id']);

        $coupon = app(CouponService::class)->getProductCouponList($product['product_id'],
            $product['shop_id'], $product['brand_id'], request()->userId, $product['category_id']);
        $userCoupon = app(UserCouponService::class)->getFilterResult([
            'size' => 10000,
            'page' => 1,
            'used_time' => 0,
            'start_date' => time(),
            'enc_time' => time(),
            'user_id' => request()->userId
        ]);
        $userCouponArr = [];
        if (!empty($userCoupon['list']) && is_array($userCoupon['list'])) {
            foreach ($userCoupon['list'] as $item) {
                $userCouponArr[] = $item['coupon_id'];
            }
        }
        $exist_coupon = [];
        foreach ($coupon as $k => $c) {
            if (in_array($c['coupon_id'], $userCouponArr)) {
                $c['is_receive'] = 1;
                $exist_coupon[] = $c;
                unset($coupon[$k]);
            } else {
                $coupon[$k]['is_receive'] = 0;
            }

        }
        $coupon = array_merge($coupon, $exist_coupon);
        return $this->success([
            'list' => $coupon,
        ]);
    }

    /**
     * 判断商品是否被收藏
     * @return \think\Response
     * @throws \exceptions\ApiException
     */
    public function isCollect(): \think\Response
    {
        $id = input('id/d', "");
        $productDetailService = new ProductDetailService($id);
        $result = $productDetailService->getIsCollect();
        return $this->success([
            'item' => $result,
        ]);
    }

}
