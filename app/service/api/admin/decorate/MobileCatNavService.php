<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 首页分类栏
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\decorate;

use app\model\decorate\MobileCatNav;
use app\model\product\Brand;
use app\model\product\Category;
use app\service\api\admin\BaseService;
use exceptions\ApiException;

/**
 * 首页分类栏服务类
 */
class MobileCatNavService extends BaseService
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
    public function getFilterResult(array $filter = []): array
    {
        $query = $this->filterQuery($filter)->with(["category"]);
        if (isset($filter["paging"]) && !empty($filter["paging"])) {
            $query = $query->page($filter['page'], $filter['size']);
        }
        $result = $query->select();

        // 分类品牌信息
        foreach ($result as $item) {
            $child_cat_ids = $item->child_cat_ids;
            $brand_ids = $item->brand_ids;
            if (!empty($child_cat_ids)) {
                $item->child_cat_info = Category::whereIn('category_id', $child_cat_ids)
                    ->column("category_id,category_name,category_pic");

            }
            if (!empty($brand_ids)) {
                $item->brand_info = Brand::whereIn('brand_id', $brand_ids)
                    ->column("brand_id,brand_name,brand_logo");
            }
        }
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
    public function filterQuery(array $filter): object
    {
        $query = MobileCatNav::query();
        // 处理筛选条件

        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->where('cat_name_alias', 'like', '%' . $filter['keyword'] . '%');
        }

        if (isset($filter['is_show']) && $filter['is_show'] > -1) {
            $query->where('is_show', $filter['is_show']);
        }

        if (isset($filter['sort_field'], $filter['sort_order']) && !empty($filter['sort_field']) && !empty($filter['sort_field']) && !empty($filter['sort_order'])) {
            $query->order($filter['sort_field'], $filter['sort_order']);
        }
        return $query;
    }

    /**
     * 获取详情
     *
     * @param int $id
     * @return MobileCatNav
     * @throws ApiException
     */
    public function getDetail(int $id): MobileCatNav
    {
        $result = MobileCatNav::with(["category"])->where('mobile_cat_nav_id', $id)->find();

        if (!$result) {
            throw new ApiException(/** LANG */'首页分类栏不存在');
        }

        return $result;
    }

    /**
     * 添加首页分类栏
     * @param array $data
     * @return int
     */
    public function createMobileCatNav(array $data):int
    {
        $result = MobileCatNav::create($data);
        $id = $result->mobile_cat_nav_id;
        return $id;
    }

    /**
     * 执行首页分类栏更新
     *
     * @param int $id
     * @param array $data
     * @return bool
     * @throws ApiException
     */
    public function updateMobileCatNav(int $id, array $data): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = MobileCatNav::where('mobile_cat_nav_id', $id)->save($data);
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
    public function updateMobileCatNavField(int $id, array $data)
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = MobileCatNav::where('mobile_cat_nav_id', $id)->save($data);
        return $result !== false;
    }

    /**
     * 删除首页分类栏
     *
     * @param int $id
     * @return bool
     */
    public function deleteMobileCatNav(int $id): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = MobileCatNav::destroy($id);
        return $result !== false;
    }
}
