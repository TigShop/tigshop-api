<?php
//**---------------------------------------------------------------------+
//** 通用接口控制器文件 -- 首页
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\api\controller\search;

use app\api\IndexBaseController;
use app\service\api\admin\product\ProductSearchService;
use think\App;

/**
 * 首页控制器
 */
class Search extends IndexBaseController
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
     * 搜索
     *
     * @return \think\Response
     */
    public function index(): \think\Response
    {
        return $this->success([
        ]);
    }

    /**
     * 获取筛选列表
     * @return \think\Response
     */
    public function getFilter(): \think\Response
    {
        $params = request()->only([
            'cat/d' => 0,
            'brand' => '',
            'keyword' => '',
            'max/d' => 0,
            'min/d' => 0,
            'intro' => ""
        ]);
        $product_search = new ProductSearchService($params, 'list');
        $filter_selected = $product_search->getFilterSeleted();
        $filter_list = $product_search->getFilterList();
        return $this->success([
            'filter' => $filter_list,
            'filter_selected' => $filter_selected,
            'total' => 0,
        ]);
    }

    /**
     * 获取筛选商品列表
     * @return \think\Response
     */
    public function getProduct(): \think\Response
    {
        $params = request()->only([
            'cat/d' => 0,
            'brand' => '',
            'max/d' => 0,
            'min/d' => 0,
            'keyword' => '',
            'sort' => '',
            'order' => '',
            'page' => 1,
            'size' => 25,
            'intro' => "",
            'coupon_id' => 0
        ]);
        $product_search = new ProductSearchService($params, 'list');
        $product_list = $product_search->getProductList();
        $total = $product_search->getProductCount();
        return $this->success([
            'product_list' => $product_list,
            'total' => $total,
        ]);
    }

}
