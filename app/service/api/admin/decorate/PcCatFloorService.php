<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- PC分类抽屉
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\decorate;

use app\model\decorate\PcCatFloor;
use app\model\product\Category;
use app\service\api\admin\BaseService;
use app\service\api\admin\product\CategoryService;
use exceptions\ApiException;

/**
 * PC分类抽屉服务类
 */
class PcCatFloorService extends BaseService
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
    public function getFilterResult(array $filter): array
    {
        $query = $this->filterQuery($filter);
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
     * 筛选查询
     *
     * @param array $filter
     * @return object
     */
    public function filterQuery(array $filter): object
    {
        $query = PcCatFloor::query();
        // 处理筛选条件

        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->where('cat_floor_name', 'like', '%' . $filter['keyword'] . '%');
        }

        if (isset($filter['is_show']) && $filter['is_show'] > -1) {
            $query->where('is_show', $filter['is_show']);
        }

        if (isset($filter['sort_field'], $filter['sort_order']) && !empty($filter['sort_field']) && !empty($filter['sort_order'])) {
            $query->order($filter['sort_field'], $filter['sort_order']);
        }
        return $query;
    }

    /**
     * 获取详情
     *
     * @param int $id
     * @return PcCatFloor
     * @throws ApiException
     */
    public function getDetail(int $id): PcCatFloor
    {
        $result = PcCatFloor::find($id);

        if (!$result) {
            throw new ApiException(/** LANG */'PC分类抽屉不存在');
        }

        return $result;
    }

    /**
     * 获取最终显示的分类名
     * @param array $data
     * @return array
     */
    public function getCatFloorName(array $data): array
    {
        // 获取最终显示的分类名
        $cat_floor_name = '';
        foreach ($data["category_ids"] as $k => $v) {
            $cate_name = Category::where(['category_id' => $v])->value('category_name');
            $cat_floor_name .= ($k == 0 ? '' : '，') . ($data["category_names"][$k] != '' ? ($data["category_names"][$k] == '-' ? '[' . $cate_name . ']' : $data["category_names"][$k]) : $cate_name);
        }
        $data["cat_floor_name"] = $cat_floor_name ?? '';
        return $data;
    }

    /**
     * 添加PC分类抽屉
     * @param array $data
     * @return int
     */
    public function createPcCatFloor(array $data): int
    {
        $data = $this->getCatFloorName($data);
        $result = PcCatFloor::create($data);
        return $result->getKey();
    }

    /**
     * 执行PC分类抽屉更新
     *
     * @param int $id
     * @param array $data
     * @return bool
     * @throws ApiException
     */
    public function updatePcCatFloor(int $id, array $data): bool
    {
        $data = $this->getCatFloorName($data);
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = PcCatFloor::where('cat_floor_id', $id)->save($data);
        return $result !== false;
    }
    /**
     * 更新单个字段
     *
     * @param int $id
     * @param array $data
     * @return bool
     * @throws ApiException
     */
    public function updatePcCatFloorField(int $id, array $data): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = PcCatFloor::where('cat_floor_id', $id)->save($data);
        return $result !== false;
    }

    /**
     * 删除PC分类抽屉
     *
     * @param int $id
     * @return bool
     */
    public function deletePcCatFloor(int $id): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = PcCatFloor::destroy($id);
        return $result !== false;
    }

    /**
     * 获取PC分类抽屉
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getCatFloor(): array
    {
        $data = cache('catFloor');
        if ($data === null || $data === false) {
            $cat_arr = [];
            $cat_floors = PcCatFloor::where('is_show', 1)->order('sort_order', 'asc')->select();
            foreach ($cat_floors as $key => $floor) {
                $cat_arr[$key]['floor_ico_font'] = $floor['floor_ico_font'];
                $cat_arr[$key]['cat_floor_id'] = $floor['cat_floor_id'];
                $cat_arr[$key]['cat_floor_name'] = $floor['cat_floor_name'];

                foreach ($floor['category_ids'] as $k => $cat_id) {
                    if ($cat_id > 0) {
                        $short_name[$cat_id] = $floor['category_names'][$k];
                    }
                }
                $cat_shown = [];
                $categories = Category::whereIn('category_id', $floor['category_ids'])->where('is_show', 1)->select();
                $idx = 0;
                foreach ($categories as $category) {
                    $cat_arr[$key]['cat_list'][$idx]['id'] = $category['category_id'];
                    $cat_arr[$key]['cat_list'][$idx]['name'] = $short_name[$category['category_id']] ? $short_name[$category['category_id']] : $category['category_name'];

                    if ($category->parent_id == 0 && !isset($cat_shown[$category['category_id']])) {
                        $cat_arr[$key]['cat_list'][$idx]['children'] = app(CategoryService::class)->catList($category['category_id']);
                        $cat_shown[$category['category_id']] = true;
                    }
                    $idx++;
                }
            }
            cache('catFloor', $cat_arr, 86400 * 100, 'cat');
        } else {
            $cat_arr = $data;
        }
        return $cat_arr;
    }
}
