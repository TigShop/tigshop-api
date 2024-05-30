<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 商品
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\product;

use app\model\promotion\Coupon;
use app\service\api\admin\BaseService;

/**
 * 商品服务类
 */
class ProductSearchService extends BaseService
{
    protected $brandIds = [];
    protected $categoryId = 0;
    protected $keyword = '';
    protected $page = 1;
    protected $size = 25;
    protected $introType = '';
    protected $minPrice = 0;
    protected $maxPrice = 0;
    protected $sortOrder = '';
    protected $sortField = '';
    protected $filterParams;
    protected $pageType;
    protected $couponId;

    public function __construct($params, $pageType = 'search')
    {
        if (isset($params['page'])) {
            $this->page = $params['page'] > 0 ? intval($params['page']) : 1;
        }
        if (isset($params['cat'])) {
            $this->categoryId = $params['cat'] > 0 ? intval($params['cat']) : 0;
        }
        if (isset($params['brand'])) {
            $this->brandIds = !empty($params['brand']) ? array_map('intval', explode(',', $params['brand'])) : [];
        }
        if (isset($params['keyword'])) {
            $this->keyword = !empty($params['keyword']) ? trim($params['keyword']) : '';
        }
        if (isset($params['sort']) && in_array($params['sort'], ['time', 'sale', 'price'])) {
            $this->sortField = !empty($params['sort']) ? trim($params['sort']) : '';
        }
        if (isset($params['order']) && in_array($params['order'], ['desc', 'asc'])) {
            $this->sortOrder = !empty($params['order']) ? trim($params['order']) : '';
        }
        if (isset($params['max'])) {
            $this->maxPrice = $params['max'] > 0 ? intval($params['max']) : 0;
        }
        if (isset($params['min'])) {
            $this->minPrice = $params['min'] > 0 ? intval($params['min']) : 0;
        }
        if (isset($params["intro"])) {
            $this->introType = !empty($params['intro']) ? trim($params['intro']) : '';
        }
        if (!empty($params['coupon_id'])) {
            $this->couponId = $params['coupon_id'];
        }


        $this->pageType = $pageType;
        $this->filterParams = [
            'brand_ids' => $this->brandIds,
            'category_id' => $this->categoryId,
            'keyword' => $this->keyword,
            'max_price' => $this->maxPrice,
            'min_price' => $this->minPrice,
            'intro_type' => $this->introType,
            'is_delete' => 0,
            'coupon_id' => $this->couponId,
        ];
    }

    public function getProductList(): array
    {
        $params = array_merge($this->filterParams, [
            'page' => $this->page,
            'size' => $this->size <= 50 ? $this->size : 25,
        ]);
        $params['sort_order'] = '';
        if ($this->sortField == '') {
            $params['sort_field'] = '';
        } elseif ($this->sortField == 'sale') {
            $params['sort_field'] = 'virtual_sales';
            $params['sort_order'] = 'desc';
        } elseif ($this->sortField == 'time') {
            $params['sort_field'] = 'product_id';
            $params['sort_order'] = 'desc';
        } elseif ($this->sortField == 'price') {
            $params['sort_field'] = 'product_price';
            $params['sort_order'] = $this->sortOrder;
        }
        $product_list = app(ProductService::class)->getProductList($params);
        return $product_list;
    }
    public function getProductCount(): int
    {
        $count = app(ProductService::class)->getFilterCount($this->filterParams);
        return $count;
    }
    // 筛选列表
    public function getFilterList(): array
    {
        $filter = [
            'category' => [],
            'brand' => [],
        ];
        if ($this->categoryId > 0) {
            $filter['category'] = app(CategoryService::class)->getChildCategoryList($this->categoryId);
        } else {
            if ($this->introType) {
                // 按类型
                $filter['category'] = app(ProductService::class)->getProductCategorys($this->filterParams);
            } else {
                if ($this->keyword) {
                    $filter['category'] = app(ProductService::class)->getProductCategorys($this->filterParams);
                } else {
                    $filter['category'] = app(CategoryService::class)->getChildCategoryList(0);
                }
            }
        }
        $params = $this->filterParams;
        unset($params['brand_ids']);
        $filter['brand'] = app(ProductService::class)->getProductBrands($params);

        return $filter;
    }
    public function getFilterSeleted(): array
    {
        $seleted = [
            'category' => '',
            'brand' => '',
            'keyword' => '',
            'intro' => '',
        ];
        if ($this->categoryId > 0) {
            $seleted['category'] = app(CategoryService::class)->getName($this->categoryId);
        }
        if ($this->brandIds) {
            $brand_names = app(BrandService::class)->getNames($this->brandIds);
            $seleted['brand'] = implode(', ', $brand_names);
        }
        if ($this->keyword) {
            $seleted['keyword'] = $this->keyword;
        }
        if ($this->introType) {
            $seleted['intro'] = $this->introType;
        }

        return $seleted;
    }
}
