<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 销售统计
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\adminapi\controller\panel;

use app\adminapi\AdminBaseController;
use app\service\api\admin\panel\SalesStatisticsService;
use think\App;
use think\Response;

/**
 * 销售统计图表控制器
 */
class SalesStatistics extends AdminBaseController
{
    protected SalesStatisticsService $salesStatisticsService;

    /**
     * 构造函数
     *
     * @param App $app
     */
    public function __construct(App $app, SalesStatisticsService $salesStatisticsService)
    {
        parent::__construct($app);
        $this->salesStatisticsService = $salesStatisticsService;
        $this->checkAuthor('statisticsSalesManage'); //权限检查
    }

    /**
     * 销售统计
     * @return Response
     * @throws \exceptions\ApiException
     */
    public function list(): Response
    {
        $filter = $this->request->only([
            "is_export/d" => 0,
            'statistic_type/d' => 1,
            'date_type/d' => 1,
            'start_end_time' => "",
        ], 'get');
        $filter['shop_id'] = request()->shopId;
        // 销售统计数据
        $filterResult = $this->salesStatisticsService->getSalesData($filter);

        return $this->success([
            'sales_data' => $filterResult["sales_data"],
            'sales_statistics_data' => $filterResult["sales_statistics_data"],
        ]);
    }

    /**
     * 销售明细
     * @return Response
     * @throws \exceptions\ApiException
     */
    public function salesDetail(): Response
    {
        $filter = $this->request->only([
            "start_time" => "",
            "end_time" => "",
        ], 'get');
        $filter['shop_id'] = request()->shopId;
        $filterResult = $this->salesStatisticsService->getSaleDetail($filter);

        return $this->success([
            'sales_data' => $filterResult["sales_data"],
            'sales_statistics_data' => $filterResult["sales_statistics_data"],
        ]);
    }

    /**
     * 销售商品明细
     * @return Response
     */
    public function salesProductDetail(): Response
    {
        $filter = $this->request->only([
            "start_time" => "",
            "end_time" => "",
            'keyword' => "",
            "is_export/d" => 0,
            'page/d' => 1,
            'size/d' => 15,
            'sort_field' => 'item_id',
            'sort_order' => 'desc',
        ], 'get');
        $filter["shop_id"] = request()->shopId;
        $filterResult = $this->salesStatisticsService->getSaleProductDetail($filter);

        return $this->success([
            'filter_result' => $filterResult["list"],
            'filter' => $filter,
            'total' => $filterResult["count"],
        ]);
    }

    /**
     * 销售指标
     * @return Response
     * @throws \think\db\exception\DbException
     */
    public function salesIndicators(): Response
    {
        $filterResult = $this->salesStatisticsService->getSaleIndicators();
        return $this->success([
            'filter_result' => $filterResult,
        ]);
    }

    /**
     * 销售排行
     * @return Response
     */
    public function salesRanking(): Response
    {
        $filter = $this->request->only([
            "start_time" => "",
            "end_time" => "",
            'keyword' => "",
            "is_export/d" => 0,
            'page/d' => 1,
            'size/d' => 15,
            'sort_field' => 'item_id',
            'sort_order' => 'desc',
        ], 'get');

        $filterResult = $this->salesStatisticsService->getSalesRanking($filter);

        return $this->success([
            'filter_result' => $filterResult["list"],
            'filter' => $filter,
            'total' => $filterResult["count"],
        ]);
    }
}
