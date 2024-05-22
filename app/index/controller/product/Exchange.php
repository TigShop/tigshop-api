<?php
//**---------------------------------------------------------------------+
//** 通用接口控制器文件 -- 商品积分兑换
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\index\controller\product;

use app\index\IndexBaseController;
use app\service\api\admin\product\ProductDetailService;
use app\service\api\admin\promotion\PointsExchangeService;
use think\App;

/**
 * 商品控制器
 */
class Exchange extends IndexBaseController
{
    protected PointsExchangeService $pointsExchangeService;
    /**
     * 构造函数
     *
     * @param App $app
     */
    public function __construct(App $app, PointsExchangeService $pointsExchangeService)
    {
        parent::__construct($app);
        $this->pointsExchangeService = $pointsExchangeService;
    }

    /**
     * 列表
     * @return \think\Response
     */
    public function list(): \think\Response
    {
        $filter = $this->request->only([
            'is_enabled/d' => 1,
            'page/d' => 1,
            'size/d' => 15,
            'sort_field' => 'id',
            'sort_order' => 'desc',
        ], 'get');

        $filterResult = $this->pointsExchangeService->getFilterResult($filter);
        $total = $this->pointsExchangeService->getFilterCount($filter);

        return $this->success([
            'filter_result' => $filterResult,
            'filter' => $filter,
            'total' => $total,
        ]);
    }

    /**
     * 详情
     * @return \think\Response
     * @throws \app\common\exceptions\ApiException
     */
    public function detail(): \think\Response
    {
        $id = input('id/d', 0);
        $item = $this->pointsExchangeService->getDetail($id);
        $productDetailService = new ProductDetailService($item["product_id"]);
        $product = $productDetailService->getDetail();
        return $this->success([
            'item' => $item,
            "product_info" => $product,
            'desc_arr' => $productDetailService->getDescArr(),
            'sku_list' => $productDetailService->getSkuList(),
            'pic_list' => $productDetailService->getPicList(),
            'attr_list' => $productDetailService->getAttrList(),
            'rank_detail' => $productDetailService->getProductCommentRankDetail(),
            'seckill_detail' => $productDetailService->getSeckillInfo(),
            'consultation_total' => $productDetailService->getConsultationCount(),
        ]);
    }

}
