<?php

namespace app\service\api\admin\product;

use app\model\product\Brand;
use app\model\product\Category;
use app\model\product\Product;
use app\model\product\ProductArticle;
use app\service\api\admin\BaseService;
use app\service\api\admin\content\ArticleService;
use app\validate\product\CategoryValidate;
use exceptions\ApiException;
use log\AdminLog;
use think\facade\Cache;
use think\facade\Db;

/**
 * 商品分类服务类
 */
class CategoryService extends BaseService
{
    protected Category $categoryModel;
    protected CategoryValidate $categoryValidate;

    public function __construct(Category $categoryModel)
    {
        $this->categoryModel = $categoryModel;
    }

    /**
     * 获取筛选结果
     *
     * @param array $filter
     * @return array
     */
    public function getFilterResult(array $filter): array
    {
        $query = $this->filterQuery($filter);
        $result = $query->field('c.*, COUNT(s.category_id) AS has_children')
            ->leftJoin('category s', 'c.category_id = s.parent_id')
            ->group('c.category_id')->page($filter['page'], $filter['size'])->select();
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
    protected function filterQuery(array $filter): object
    {
        $query = $this->categoryModel->query()->alias('c');
        // 处理筛选条件

        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->where('c.category_name', 'like', '%' . $filter['keyword'] . '%');
        }

        $query->where('c.parent_id', $filter['parent_id']);

        if (isset($filter['sort_field'], $filter['sort_order']) && !empty($filter['sort_field']) && !empty($filter['sort_order'])) {
            $query->order($filter['sort_field'], $filter['sort_order']);
        }
        return $query;
    }

    /**
     * 获取分类详情
     *
     * @param int $id
     * @return array
     * @throws ApiException
     */
    public function getDetail(int $id): array
    {
        $result = $this->categoryModel->where('category_id', $id)->find();

        if (!$result) {
            throw new ApiException('分类不存在');
        }

        return $result->toArray();
    }

    /**
     * 获取分类名称
     *
     * @param int $id
     * @return string|null
     */
    public function getName(int $id): ?string
    {
        return $this->categoryModel::where('category_id', $id)->value('category_name');
    }

    /**
     * 执行分类添加或更新
     *
     * @param int $id
     * @param array $data
     * @param bool $isAdd
     * @return int|bool
     * @throws ApiException
     */
    public function updateCategory(int $id, array $data, bool $isAdd = false)
    {
        validate(CategoryValidate::class)->only(array_keys($data))->check($data);
        if ($isAdd) {
            $result = $this->categoryModel->save($data);
            AdminLog::add('新增分类:' . $data['category_name']);
            return $this->categoryModel->getKey();
        } else {
            $result = $this->categoryModel->where('category_id', $id)->save($data);
            AdminLog::add('更新分类:' . $this->getName($id));

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
    public function updateCategoryField(int $id, array $data)
    {
        validate(CategoryValidate::class)->only(array_keys($data))->check($data);
        if (!$id) {
            throw new ApiException('#id错误');
        }
        $result = $this->categoryModel::where('category_id', $id)->save($data);
        AdminLog::add('更新分类:' . $this->getName($id));
        return $result !== false;
    }

    /**
     * 删除分类
     *
     * @param int $id
     * @return bool
     */
    public function deleteCategory(int $id): bool
    {
        $get_name = $this->getName($id);
        $result = $this->categoryModel::destroy($id);

        if ($result) {
            Db::name('product')->where('category_id', $id)->update(['category_id' => 0]);
            AdminLog::add('删除分类:' . $get_name);
        }

        return $result !== false;
    }

    /**
     * 商品转移
     *
     * @param int $id
     * @return bool
     */
    public function moveCat(int $id, int $target_id): bool
    {
        if (!Category::find($id) || !Category::find($target_id)) {
            throw new ApiException('请选择正确的商品分类！');
        }

        $result = Db::name('product')->where('category_id', $id)->update(['category_id' => $target_id]);

        return $result !== false;
    }

    /**
     * 获取所有分类列表 新方法
     * @param int $category_id 获取该分类id下的所有分类（不含该分类）
     * @param bool $return_ids 是否返回分类id列表
     * @return array
     */
    public function catList(int $category_id = 0): array
    {
        $data = cache('catList');
        if ($data === null || $data === false) {
            $cat_list = $this->categoryModel->alias('c')->field('c.category_id, c.category_name, c.parent_id')
                ->order('c.parent_id, c.sort_order ASC, c.category_id ASC')->where("c.is_show", 1)->select();
            $cat_list = $cat_list ? $cat_list->toArray() : [];
            cache('catList', $cat_list, 86400 * 100, 'cat');
        } else {
            $cat_list = $data;
        }
        $res = $this->xmsbGetDataTree($cat_list, $category_id);
        return (array) $res;
    }

    /**
     * 获取指定分类id下的所有子分类id列表
     * @param int $category_id
     * @return int[]
     */
    public function catAllChildIds(int $category_id = 0): array
    {
        $cat_list = $this->catList($category_id);
        $ids = [$category_id];

        // 判断是否为二维数组
        if (count($cat_list) !== count($cat_list, COUNT_RECURSIVE)) {
            $this->getChildrenIds($cat_list, $ids);
        }

        return $ids;
    }

    /**
     * 递归查找分类
     * @param $category
     * @param $ids
     * @return void
     */
    public function getChildrenIds($category, &$ids)
    {
        foreach ($category as $key => $value) {
            $ids[] = $value['category_id'];
            if (isset($value['children'])) {
                $this->getChildrenIds($value['children'], $ids);
            }
        }
    }

    /**
     * 无限级分类函数
     * @param array $arr 查询出的数据
     * @param int $first_parent 根节点主键值
     * @return array
     */
    public function xmsbGetDataTree(array $arr, int $first_parent = 0): array
    {
        $tree = ['category_id' => 0, 'parent_id' => 0];
        $tmpMap = [$first_parent => &$tree];
        foreach ($arr as $rk => $rv) {
            $tmpMap[$rv['category_id']] = $rv;
            $parentObj = &$tmpMap[$rv['parent_id']];
            if (!isset($parentObj['children'])) {
                $parentObj['children'] = [];
            }
            $parentObj['children'][] = &$tmpMap[$rv['category_id']];
        }
        if (!isset($tree['children'])) {
            return (array) $tree;
        }
        return (array) $tree['children'];
    }

    /**
     * 获取指定分类id下一级的所有子分类id和name
     * @param int $category_id 分类id
     * @return array
     */
    public function getChildCategoryList(int $parent_id = 0): array
    {
        $result = $this->categoryModel->field('category_id, category_name')->where('parent_id', $parent_id)->select();
        return $result->toArray();
    }

    /**
     * 获取所有父分类
     * @param int $category_id 分类id
     * @return array
     */
    public function getAllParentCategoryInfo(int $category_id): array
    {
        $data = [];
        while ($category_id) {
            $result = $this->categoryModel::field('category_id,parent_id,category_name')->find($category_id);
            if ($result) {
                $category_id = $result->parent_id;
                $data[] = $result->toArray();
            } else {
                $category_id = 0;
            }
        }
        return $data;
    }

    //获取当前分类的父级分类(每个父级都会获取同级其它分类  -  主要是分类页筛选使用)
    public function getParentCategoryTree($category_id = 0): array
    {
        $ids = 0;
        $parent_id = $category_id;
        $data = [];
        while ($parent_id > 0) {
            $result = $this->categoryModel::field('category_id,parent_id,category_name')->find($parent_id);
            if ($result) {
                $parent_id = $result->parent_id;
                $data[$ids] = $result->toArray();
                $ids++;
            } else {
                $parent_id = 0;
            }
        }
        $data = array_reverse($data);
        foreach ($data as $key => $value) {
            //查找同级分类
            $result = $this->categoryModel::where('parent_id', $value['parent_id'])->where('is_show', 1)->field('category_id,parent_id,category_name')->select()->toArray();
            $data[$key]['cat_list'] = $result;
        }
        return $data;

    }

    /**
     * 根据分类id查找上级分类
     * @param int $category_id
     * @return array[]
     */
    public function getParentCategory(int $category_id): array
    {
        $category_name = [];
        $category_ids = [];
        while ($category_id != 0) {
            $result = $this->categoryModel
                ->field('category_id, category_name,parent_id')
                ->where('category_id', $category_id)
                ->findOrEmpty()
                ->toArray();
            if (!empty($result)) {
                array_unshift($category_name, $result['category_name']);
                array_unshift($category_ids, $result['category_id']);
                $category_id = $result['parent_id'];
            } else {
                break;
            }
        }
        return [
            'category_name' => $category_name,
            'category_ids' => $category_ids,
        ];
    }

    /**
     * 获取同级分类
     * @param int $category_id
     * @param int $size
     * @return array
     * @throws ApiException
     */
    public function getRelatedCategory(int $id, array $filter): array
    {
        $cate_info = Category::where("is_show", 1)->field("category_id,category_name,parent_id")->find($id);
        if (empty($cate_info)) {
            return [];
        }
        $result = Category::where("parent_id", $cate_info->parent_id)->where("is_show", 1)
            ->limit($filter["size"])->order("sort_order", "asc")->column("category_id,category_name");
        return $result;
    }

    /**
     * 获取同类其他品牌
     * @param int $category_id
     * @param int $size
     * @return array
     * @throws ApiException
     */
    public function getOtherBrand(int $category_id, array $filter): array
    {
        $cate_ids = $this->getChilderIds($category_id);
        $brand_ids = Product::whereIn("category_id", $cate_ids)->where(["is_delete" => 0, "product_status" => 1])
            ->group("brand_id")->column("brand_id");
        $result = Brand::where("brand_id", "in", $brand_ids)
            ->field("brand_id,brand_name,brand_logo,site_url,first_word")
            ->where("is_show", 1)
            ->order("sort_order", "asc")
            ->limit($filter["size"])
            ->select()->toArray();
        return $result;
    }

    /**
     * 获取当前分类所属顶级分类下的所有分类
     * @param int $category_id
     * @return int[]
     */
    public function getChilderIds(int $category_id): array
    {
        // 获取当前分类所属顶级分类下的所有分类
        $top_ids = $this->getAllParentCategoryInfo($category_id);
        $top_ids = array_column($top_ids, "category_id");
        // 获取所有子集
        $cate_ids = $this->catAllChildIds(end($top_ids));
        return $cate_ids;
    }

    /**
     * 获取同类排行榜
     * @param int $category_id
     * @param array $filter
     * @return array[]
     * @throws ApiException
     */
    public function getCategoryRank(int $category_id, array $filter): array
    {
        // 判断缓存是否存在
        if (Cache::has("category_rank_" . $category_id . "_" . $filter["product_id"])) {
            return Cache::get("category_rank_" . $category_id . "_" . $filter["product_id"]);
        }

        $product_info = Product::find($filter["product_id"]);
        if (empty($product_info)) {
            throw new ApiException("#product_id错误");
        }

        $top_ids = $this->getChilderIds($category_id);

        $query = Product::where("product_id", "<>", $filter["product_id"])
            ->where("is_delete", 0)
            ->where("product_status", 1)
            ->limit($filter["rank_num"])
            ->field("product_id,product_name,product_sn,market_price,pic_thumb");

        $price_product = $this->getProductInfo($query, $product_info, 1, $top_ids);
        $brand_product = $this->getProductInfo($query, $product_info, 2);
        $cate_product = $this->getProductInfo($query, $product_info, 3);

        $result = [
            "price" => $price_product,
            "brand" => $brand_product,
            "cate" => $cate_product,
        ];
        // 设置缓存
        Cache::set("category_rank_" . $category_id . "_" . $filter["product_id"], $result, 120);
        return $result;
    }

    /**
     * 获取对应排行数据
     * @param object $query
     * @param array $cate_ids
     * @param object $product
     * @param int $type
     * @return array
     */
    public function getProductInfo(object $query, object $product, int $type, array $cate_ids = []): array
    {
        switch ($type) {
            case 1: //同价位 -- 价格接近
                $price_product = clone $query;
                $result = $price_product->whereIn("category_id", $cate_ids)
                    ->orderRaw('ABS(market_price - ' . $product->market_price . ') asc')
                    ->select()->toArray();
                break;
            case 2: // 同品牌
                $brand_product = clone $query;
                $result = $brand_product->where("brand_id", $product->brand_id)
                    ->order("sort_order", "asc")
                    ->select()->toArray();
                break;
            case 3: // 同类别
                $cate_product = clone $query;
                $result = $cate_product->where("category_id", $product->category_id)
                    ->order("sort_order", "asc")
                    ->select()->toArray();
                break;
        }
        return $result;
    }

    /**
     * 获取商品相关文章列表
     * @param array $filter
     * @return array
     */
    public function getArticleList(array $filter): array
    {
        // 获取文章id
        $article_ids = ProductArticle::where("goods_id", $filter["product_id"])->column("article_id");
        if (empty($article_ids)) {
            return [];
        }
        $result = app(ArticleService::class)->getFilterList(["article_ids" => $article_ids, "size" => $filter["size"]]);

        return $result["list"];
    }

    /**
     * 看了还看 -- 子分类下的热销品
     * @param int $category_id
     * @param array $filter
     * @return array
     * @throws ApiException
     */
    public function getLookAlso(int $category_id, array $filter): array
    {
        $cate_ids = $this->catAllChildIds($category_id);
        $result = Product::whereIn("category_id", $cate_ids)
            ->where("is_delete", 0)
            ->where("product_status", 1)
            ->introType($filter["intro"])
            ->limit($filter["size"])
            ->field("product_id,product_name,product_sn,market_price,pic_thumb")
            ->order("sort_order", "asc")
            ->select()->toArray();

        return $result;
    }

    /**
     * 热门分类
     * @return Category[]|array|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getHotCategory()
    {
        return Category::where('is_show', 1)->where('is_hot', 1)->order('sort_order', 'desc')->limit(20)->select();
    }

}
