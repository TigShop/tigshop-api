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

namespace app\service\product;

use app\common\exceptions\ApiException;
use app\common\log\AdminLog;
use app\common\utils\Config;
use app\common\utils\Time;
use app\model\product\Product;
use app\model\product\ProductArticle;
use app\model\product\ProductSku;
use app\service\BaseService;
use app\service\participle\ParticipleService;
use app\service\promotion\SeckillService;
use app\validate\product\ProductValidate;
use think\facade\Db;

/**
 * 商品服务类
 */
class ProductService extends BaseService
{
    protected ProductValidate $productValidate;

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
        $filter['page'] = !empty($filter['page']) ? intval($filter['page']) : 1;
        $filter['size'] = !empty($filter['size'] && $filter['size'] < 999) ? intval($filter['size']) : 999;
        $query = $this->filterQuery($filter)
            ->field('category_id,brand_id,product_tsn,market_price,shipping_tpl_id,free_shipping,product_id,pic_thumb,product_name,check_status,store_id,suppliers_id,product_type,product_sn,product_price,product_status,is_best,is_new,is_hot,product_stock,sort_order');
        if (isset($filter['sort_field_raw']) && !empty($filter['sort_field_raw'])) {
            $query->orderRaw($filter['sort_field_raw']);
        } elseif (isset($filter['sort_field']) && !empty($filter['sort_field'])) {
            $query->order($filter['sort_field'], $filter['sort_order'] ?? 'desc');
        } else {
            $query->order('sort_order', 'asc')->order('product_id', 'desc');
        }
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
     * 获取商品列表
     * @param array $filter
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getProductList(array $filter): array
    {
        $filter['page'] = !empty($filter['page']) ? intval($filter['page']) : 1;
        $filter['size'] = !empty($filter['size'] && $filter['size'] < 999) ? intval($filter['size']) : 999;
        $filter['product_status'] = 1;
        $query = $this->filterQuery($filter)->with(['seckillMinPrice', "product_sku"])
            ->field('product_id,pic_thumb,pic_url,product_name,check_status,store_id,suppliers_id,product_type,product_sn,product_price,market_price,product_status,is_best,is_new,is_hot,product_stock,sort_order');
        if (isset($filter['sort_field']) && !empty($filter['sort_field'])) {
            $query->order($filter['sort_field'], $filter['sort_order'] ?? 'desc');
        } else {
            $query->order('sort_order', 'asc')->order('product_id', 'desc');
        }
        $result = $query->page($filter['page'], $filter['size'])->select();
        foreach ($result as $value) {
            if ($value->seckill_price > 0) {
                $value->org_product_price = $value->product_price;
                $value->product_price = $value->seckill_price;
                $value->market_price = $value->org_product_price;
            }
        }
        return $result->toArray();
    }

    /**
     * 筛选查询
     *
     * @param array $filter
     * @return object
     */
    public function filterQuery(array $filter): object
    {
        $query = Product::query();
        // 处理筛选条件

        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->where(function ($query) use ($filter) {
                $query->where('product_name', 'like', '%' . $filter['keyword'] . '%')
                    ->whereOr('product_sn', 'like', '%' . $filter['keyword'] . '%');
//                $participle = app(ParticipleService::class)->cutForSearch($filter['keyword'], true);
//                $keyword_where = implode('|', $participle);
//                $query->where('product_name', 'exp', " REGEXP '$keyword_where' ");
            });
        }

        if (isset($filter['is_show']) && $filter['is_show'] > -1) {
            $query->where('is_show', $filter['is_show']);
        }

        if (isset($filter['brand_id']) && $filter['brand_id'] > 0) {
            $query->where('brand_id', $filter['brand_id']);
        }

        if (isset($filter['brand_ids']) && is_array($filter['brand_ids']) && !empty($filter['brand_ids'])) {
            $query->whereIn('brand_id', $filter['brand_ids']);
        }

        if (isset($filter['product_id']) && $filter['product_id'] > 0) {
            $query->where('product_id', $filter['product_id']);
        }
        if (isset($filter['product_ids']) && !empty($filter['product_ids'])) {
            $query->whereIn('product_id', $filter['product_ids']);
        }

        if (isset($filter['category_id']) && $filter['category_id'] > 0) {
            $query->whereIn('category_id', app(CategoryService::class)->catAllChildIds($filter['category_id']));
        }

        if (isset($filter['ids']) && $filter['ids'] !== null) {
            $query->whereIn('product_id', $filter['ids']);
        }
        if (isset($filter['max_price']) && $filter['max_price'] > 0) {
            $query->where('product_price', '<=', $filter['max_price']);
        }
        if (isset($filter['min_price']) && $filter['min_price'] > 0) {
            $query->where('product_price', '>=', $filter['min_price']);
        }

        // 店铺id
        if (isset($filter["store_id"]) && $filter["store_id"] != -2) {
            if ($filter["store_id"] == -1) {
                // 店铺商品列表
                $query->where('store_id', ">", 0);
            } else {
                $query->where('store_id', $filter["store_id"]);
            }
        }

        // 商品类型
        if (isset($filter["intro_type"]) && !empty($filter["intro_type"])) {
            $query->introType($filter["intro_type"]);
        }

        // 商品上下架
        if (isset($filter["product_status"]) && $filter["product_status"] != -1) {
            $query->where('product_status', $filter["product_status"]);
        }
        //是否删除
        if (isset($filter["is_delete"]) && $filter["is_delete"] != -1) {
            $query->where('is_delete', $filter["is_delete"]);
        }
        // 审核状态
        if (isset($filter["check_status"]) && $filter["check_status"] != -1) {
            $query->where('check_status', $filter["check_status"]);
        }
        return $query;
    }

    /**
     * 获取筛选商品结果所包含的所有品牌
     * @param array $filter
     * @param int $size
     * @return array
     */
    public function getProductBrands(array $filter, int $size = 100): array
    {
        $query = $this->filterQuery($filter)->with(['brand'])->field('brand_id')->where('brand_id', '>', 0);
        $result = $query->group('brand_id')->limit($size)->select();
        return $result->toArray();
    }

    /**
     * 获取筛选商品结果所包含的所有分类
     * @param array $filter
     * @param int $size
     * @return array
     */
    public function getProductCategorys(array $filter, int $size = 20): array
    {
        $query = $this->filterQuery($filter)->with(['category'])->field('category_id')->where('category_id', '>', 0);
        $result = $query->group('category_id')->limit($size)->select();
        return $result->toArray();
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
        $result = Product::where('product_id', $id)->find();

        if (!$result) {
            throw new ApiException('商品不存在2');
        }
        $item = $result->toArray();

        $item['product_service_ids'] = $item['product_service_ids'] ?? [];
        $item['product_desc_arr'] = $this->getProductDescArr($item['product_desc']);
        $item['img_list'] = app(ProductGalleryService::class)->getProductGalleryList($id);

        return $item;
    }

    /**
     * 根据条码获取商品信息
     * @param string $goods_sn
     * @return int[]
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getProductKeyBySn(string $goods_sn): array
    {
        $product_id = 0;
        $sku_id = 0;
        $result = Product::where('product_sn', $goods_sn)->find();
        if ($result) {
            $product_id = $result->product_id;
        } else {
            $sku_info = app(ProductSkuService::class)->getProductSkuBySn($goods_sn);
            if (!empty($sku_info)) {
                $product_id = $sku_info['product_id'];
                $sku_id = $sku_info['sku_id'];
            }
        }

        return [$product_id, $sku_id];
    }

    /**
     * 获取名称
     *
     * @param int $id
     * @return string|null
     */
    public function getName(int $id): ?string
    {
        return Product::where('product_id', $id)->value('product_name');
    }

    /**
     * 执行商品添加或更新
     *
     * @param int $id
     * @param array $data
     * @param bool $isAdd
     * @return int|bool
     * @throws ApiException
     */
    public function updateProduct(int $id, array $data, bool $isAdd = false)
    {
        validate(ProductValidate::class)->only(array_keys($data))->check($data);
        if (isset($data["promote_start_date"], $data["promote_end_date"]) && !empty($data["promote_start_date"]) && !empty($data["promote_end_date"])) {
            $data["promote_start_date"] = Time::toTime($data["promote_start_date"]);
            $data["promote_end_date"] = Time::toTime($data["promote_end_date"]);
        }
        if (empty($data['keywords'])) {
            $data['keywords'] = app(ParticipleService::class)->cutForSearch($data['product_name']);
        }
        $data['product_desc'] = $this->getProductDesc($data['product_desc_arr']);
        unset($data['product_desc_arr']);
        if ($isAdd) {
            if (request()->storeId > 0) {
                if (Config::get('store_goods_need_check') == 1) {
                    $data['check_status'] = 0;
                    $data['product_status'] = 0;
                } else {
                    $data['check_status'] = 1;
                    $data['product_status'] = 1;
                }
                $data['store_id'] = request()->storeId;
            }
            $data['add_time'] = Time::now();
            if (empty($data['product_sn'])) {
                $data['product_sn'] = $this->creatNewProductSn();
            }
            unset($data["product_id"]);

            $result = Product::create($data);

            // 关联文章
            if (!empty($data["product_article_list"])) {
                foreach ($data["product_article_list"] as $k => $v) {
                    $article_list[$k] = [
                        "goods_id" => $result->product_id,
                        "article_id" => $v,
                    ];
                }
                (new ProductArticle)->saveAll($article_list);
            }

            AdminLog::add('新增商品:' . $data['product_name']);
            return $result->product_id;
        } else {
            $item = $this->getDetail($id);

            if (request()->storeId > 0) {
                // 店铺
                unset($data['is_best']);
                unset($data['is_new']);
                unset($data['is_hot']);
                unset($data['suppliers_id']);
                unset($data['give_integral']);
                unset($data['rank_integral']);
                unset($data['integral']);
                unset($data['virtual_sales']);
                unset($data['check_status']);
                if ($item['check_status'] != 1) {
                    $data['product_status'] = 0;
                }
            } elseif (request()->suppliersId > 0) {
                // 供应商
                unset($data['suppliers_id']);
                unset($data['store_cat_id']);
            } else {
                if (isset($data['check_status']) && $data['check_status'] != 1) {
                    $data['product_status'] = 0;
                }
                unset($data['store_cat_id']);
            }
            unset($data['product_type']);

            if (!$id) {
                throw new ApiException('#id错误');
            }
            $product_article_list = $data["product_article_list"];
            unset($data["product_article_list"]);

            $result = Product::where('product_id', $id)->save($data);
            ProductArticle::where('goods_id', $id)->delete();
            if (!empty($product_article_list)) {
                $article_list = [];
                foreach ($product_article_list as $k => $v) {
                    $article_list[$k] = [
                        "goods_id" => $id,
                        "article_id" => $v,
                    ];
                }
                (new ProductArticle)->saveAll($article_list);
            }

            AdminLog::add('更新商品:' . $this->getName($id));

            return $result !== false;
        }
    }

    /**
     * 更新单个字段
     *
     * @param int $id
     * @param array $data
     * @return int|bool
     * @throws ApiException
     */
    public function updateProductField(int $id, array $data)
    {
        validate(ProductValidate::class)->only(array_keys($data))->check($data);
        if (!$id) {
            throw new ApiException('#id错误');
        }
        $result = Product::where('product_id', $id)->save($data);
        AdminLog::add('更新商品:' . $this->getName($id));
        return $result !== false;
    }

    /**
     * 移至回收站
     *
     * @param int $id
     * @return bool
     */
    public function recycleProduct(int $id): bool
    {
        if (!$id) {
            throw new ApiException('#id错误');
        }
        $get_name = $this->getName($id);
        $result = Product::where('product_id', $id)->update(['is_delete' => 1]);
        if ($result) {
            AdminLog::add('移至回收站:' . $get_name);
        }

        return $result !== false;
    }

    /**
     * 删除商品
     *
     * @param int $id
     * @return bool
     */
    public function deleteProduct(int $id): bool
    {
        if (!$id) {
            throw new ApiException('#id错误');
        }
        $get_name = $this->getName($id);
        $result = Product::destroy($id);
        if ($result) {
            AdminLog::add('删除商品:' . $get_name);
        }

        return $result !== false;
    }

    /**
     * 获取商品详情
     * @param string $html
     * @return array
     */
    public static function getProductDescArr(string $html = ''): array
    {
        $res = [];
        if (empty($html)) {
            return $res;
        }
        $arr = explode('<div data-division=1></div>', $html);
        foreach ($arr as $key => $value) {
            if (strpos($value, 'desc-pic-item') !== false) {
                $res[$key]['type'] = 'pic';
                preg_match('~<img.*?src=["\']+(.*?)["\']+~', $value, $match);
                $res[$key]['pic'] = $match[1];
            } else {
                $res[$key]['type'] = 'text';
            }
            $res[$key]['html'] = $value;
        }
        return $res;
    }

    /**
     * 获取商品描述
     * @param array $product_desc_arr
     * @return string
     */
    public function getProductDesc(array $product_desc_arr): string
    {
        $html = '';
        if (empty($product_desc_arr)) {
            return '';
        }
        $res = [];
        foreach ($product_desc_arr as $key => $value) {
            if ($value['type'] == 'pic') {
                $res[$key] = "<div class=\"desc-pic-item\"><img src=\"" . $value['pic'] . "\"></div>";
            }
            if ($value['type'] == 'text') {
                $res[$key] = $value['html'];
            }
        }
        return implode('<div data-division=1></div>', $res);
    }

    /**
     * 获得商品的相关商品
     * @param int $product_id
     * @return array
     */
    public function getLinkedProductIds(int $product_id): array
    {
        $ids = Db::name('link_product')->where('product_id', $product_id)->column('link_product_id');
        return $ids;
    }

    /**
     * 获得商品的库存
     * @param int $product_id
     * @param int $sku_id
     * @return int
     */
    public function getProductStock(int $product_id, int $sku_id = 0): int
    {
        if ($sku_id == 0) {
            $result = Product::where('product_id', $product_id)->value('product_stock');
        } else {
            $result = ProductSku::where(['product_id' => $product_id, 'sku_id' => $sku_id])->value('sku_stock');
        }

        return $result ?: 0;
    }

    /**
     * 检查商品的库存是否充足
     * @param int $quantity
     * @param int $product_id
     * @param int $sku_id
     * @return bool
     */
    public function checkProductStock(int $quantity, int $product_id, int $sku_id = 0): bool
    {
        $product_stock = $this->getProductStock($product_id, $sku_id);
        return $quantity <= $product_stock;
    }

    /**
     * 待审核商品数量
     * @return int
     * @throws \think\db\exception\DbException
     */
    public function getWaitingCheckedCount(): int
    {
        return Product::where('check_status', Product::CHECK_STATUS_PENDING)->count();
    }

    /**
     * 创建商品编号
     * @param int $num
     * @return string
     */
    public function creatNewProductSn(int $num = 0): string
    {
        if (!$num) {
            $num = Product::max('product_id');
            $num = $num ? $num + 1 : 1;
        }
        $goods_sn = Config::get('sn_prefix') . str_repeat('0', 7 - strlen($num)) . $num;
        return $goods_sn;
    }

    /**
     * 减库存
     * @param int $product_id
     * @param int $quantity
     * @return bool
     */
    public function decStock(int $product_id, int $quantity): bool
    {
        return Product::where('product_id', $product_id)->dec('product_stock', $quantity)->update();
    }

    /**
     * 增加库存
     * @param int $product_id
     * @param int $quantity
     * @return bool
     */
    public function incStock(int $product_id, int $quantity): bool
    {
        return Product::where('product_id', $product_id)->dec('product_stock', $quantity)->update();
    }

    /**
     * 增加销量
     * @param int $product_id
     * @param int $quantity
     * @return bool
     */
    public function incSales(int $product_id, int $quantity): bool
    {
        return Product::where('product_id', $product_id)->inc('virtual_sales', $quantity)->update();
    }

    /**
     * 减少销量
     * @param int $product_id
     * @param int $quantity
     * @return bool
     */
    public function decSales(int $product_id, int $quantity): bool
    {
        $virtual_sales = Product::where('product_id', $product_id)->value('virtual_sales');
        if ($virtual_sales < $quantity) {
            $quantity = $virtual_sales;
        }
        return Product::where('product_id', $product_id)->dec('virtual_sales', $quantity)->update();
    }

    /**
     * 获取商品编码
     * @param int $product_id
     * @return string
     */
    public function getProductSn(int $product_id): string
    {
        return Product::where('product_id', $product_id)->value('product_sn');
    }
}
