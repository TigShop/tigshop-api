<?php

namespace app\service\api\admin\merchant;

use app\model\merchant\ShopProductCategory;
use app\model\product\Brand;
use app\model\product\Category;
use app\model\product\Product;
use app\model\product\ProductArticle;
use app\service\core\BaseService;
use exceptions\ApiException;
use log\AdminLog;
use think\facade\Cache;
use think\facade\Db;

/**
 * 商品分类服务类
 */
class ShopProductCategoryService extends BaseService
{

    public function __construct(ShopProductCategory $shopProductCategory)
    {
        $this->model = $shopProductCategory;
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
     * 筛选查询
     *
     * @param array $filter
     * @return object
     */
    protected function filterQuery(array $filter): object
    {
        $query = $this->model->query()->alias('c');
        // 处理筛选条件

        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->where('c.category_name', 'like', '%' . $filter['keyword'] . '%');
        }
        if (isset($filter['parent_id'])) {
            $query->where('c.parent_id', $filter['parent_id']);
        }
        if (isset($filter['shop_id'])) {
            $query->where('c.shop_id', $filter['shop_id']);
        }
        if (isset($filter['sort_field'], $filter['sort_order']) && !empty($filter['sort_field']) && !empty($filter['sort_order'])) {
            $query->order($filter['sort_field'], $filter['sort_order']);
        }
        return $query;
    }

    /**
     * 获取分类详情
     *
     * @param int $id
     * @return ShopProductCategory
     * @throws ApiException
     */
    public function getDetail(int $id): ShopProductCategory
    {
        $result = $this->model->where('category_id', $id)->find();

        if (!$result) {
            throw new ApiException('分类不存在');
        }

        return $result;
    }

    /**
     * 获取分类名称
     *
     * @param int $id
     * @return string|null
     */
    public function getName(int $id): ?string
    {
        return $this->model->where('category_id', $id)->value('category_name');
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
        if ($isAdd) {
            $result = $this->model->save($data);
            return $this->model->getKey();
        } else {
            $result = $this->model->where('category_id', $id)->save($data);
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
        if (!$id) {
            throw new ApiException('#id错误');
        }
        $result = $this->model::where('category_id', $id)->save($data);
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
        $result = $this->model::destroy($id);

        if ($result) {
            Db::name('product')->where('shop_category_id', $id)->update(['category_id' => 0]);
        }

        return $result !== false;
    }


    /**
     * 获取所有分类列表 新方法
     * @param int $category_id 获取该分类id下的所有分类（不含该分类）
     * @param bool $return_ids 是否返回分类id列表
     * @return array
     */
    public function catList(int $category_id = 0, int $shopId): array
    {
        $cat_list = $this->model->alias('c')->field('c.category_id, c.category_name, c.parent_id')
            ->order('c.parent_id, c.sort_order ASC, c.category_id ASC')->where('shop_id', $shopId)->where("c.is_show",
                1)->select();
        $cat_list = $cat_list ? $cat_list->toArray() : [];
        $res = $this->xmsbGetDataTree($cat_list, $category_id);
        return (array)$res;
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
            return (array)$tree;
        }
        return (array)$tree['children'];
    }

    /**
     * 获取指定分类id下一级的所有子分类id和name
     * @param int $category_id 分类id
     * @return array
     */
    public function getChildCategoryList(int $parent_id = 0): array
    {
        $result = $this->model->field('category_id, category_name')->where('parent_id', $parent_id)->select();
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
            $result = $this->model->field('category_id,parent_id,category_name')->find($category_id);
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
            $result = $this->model->field('category_id,parent_id,category_name')->find($parent_id);
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
            $result = $this->model->where('parent_id', $value['parent_id'])->where('is_show',
                1)->field('category_id,parent_id,category_name')->select()->toArray();
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
            $result = $this->model
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


}
