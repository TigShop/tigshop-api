<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 地区管理
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\setting;

use app\model\setting\Region;
use app\service\core\BaseService;
use exceptions\ApiException;
use log\AdminLog;
use utils\Config;

/**
 * 地区管理服务类
 */
class RegionService extends BaseService
{
    protected Region $regionModel;

    public function __construct(Region $regionModel)
    {
        $this->regionModel = $regionModel;
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
    protected function filterQuery(array $filter): object
    {
        $query = $this->regionModel->query();
        // 处理筛选条件
        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->where('region_name', 'like', '%' . $filter['keyword'] . '%');
        }

        // 父级查询
        if (isset($filter['parent_id']) && $filter['parent_id'] >= 0) {
            $query->where('parent_id', $filter['parent_id']);
        }

        if (isset($filter['region_id']) && $filter["region_id"] != '') {
            $query->where('region_id', $filter['region_id']);
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
     * @return Region
     * @throws ApiException
     */
    public function getDetail(int $id): Region
    {
        $result = Region::where('region_id', $id)->find();

        if (!$result) {
            throw new ApiException(/** LANG */'地区管理不存在');
        }

        return $result;
    }

    /**
     * 获取名称
     *
     * @param int $id
     * @return string|null
     */
    public function getName(int $id): ?string
    {
        return $this->regionModel::where('region_id', $id)->value('region_name');
    }

    /**
     * 获取名称集合
     * @param array $region_ids
     * @return array|null
     */
    public function getNames(array $region_ids): ?array
    {
        return $this->regionModel::whereIn('region_id', $region_ids)->column('region_name');
    }

    /**
     * 添加地区
     * @param array $data
     * @return int
     */
    public function createRegion(array $data): int
    {
        $result = $this->regionModel->save($data);
        AdminLog::add('新增地区管理:' . $data['region_name']);
        return $this->regionModel->getKey();
    }

    /**
     * 执行地区管理更新
     * @param int $id
     * @param array $data
     * @return bool
     * @throws ApiException
     */
    public function updateRegion(int $id, array $data): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = Region::where('region_id', $id)->save($data);
        AdminLog::add('更新地区管理:' . $this->getName($id));
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
    public function updateRegionField(int $id, array $data)
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = Region::where('region_id', $id)->save($data);
        AdminLog::add('更新地区管理:' . $this->getName($id));
        return $result !== false;
    }

    /**
     * 删除地区管理
     *
     * @param int $id
     * @return bool
     */
    public function deleteRegion(int $id): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $get_name = $this->getName($id);
        $result = $this->regionModel::destroy($id);

        if ($result) {
            AdminLog::add('删除地区管理:' . $get_name);
        }

        return $result !== false;
    }

    /**
     * 递归获取子地区
     *
     * @param int $parentId
     * @return array
     */
    public function getChildRegion($parentId): array
    {
        $children = Region::where('parent_id', $parentId)->withCount('children')->append(['leaf'])->select();
        $result = $children ? $children->toArray() : [];
        foreach ($result as $key => $value) {
            $result[$key]['children'] = [];
        }
        return $result;
    }

    /**
     * 通过region_ids获取列表和名称
     * []   [1,2]
     * @param array $region_ids
     * @return array
     */
    public function getRegionByIds(array $region_ids): array
    {
        $res = array();
        $region_list = [];
        $default_country = intval(Config::get('default_country'));
        if (isset($region_ids[0])) {
            array_unshift($region_ids, Region::where('region_id', $region_ids[0])->value('parent_id'));
        } else {
            array_unshift($region_ids, $default_country);
        }
        foreach ($region_ids as $key => $region_id) {
            // 获取该 parent_id 下的所有地区
            $regions = Region::where('parent_id', $region_id)->field('region_id,region_name,level')->select();
            $regions = $regions ? $regions->toArray() : [];
            if ($regions) {
                $region_list[] = $regions;
            }

        }
        return $region_list;
    }

    /**
     * 通过id获取所有地区列表【暂未使用此方法，前端插件可通过逐级请求getChildRegion方法获取完整树】
     * @param array $regions
     * @return array
     */
    public function getRegionTree(array $regions): array
    {
        $res = array();
        $top_parent_id = 0;
        foreach ($regions as $key => $region_id) {
            // 获取该 parent_id 下的所有地区
            $parent_id = Region::where('region_id', $region_id)->value('parent_id');
            if ($key == 0) {
                $top_parent_id = $parent_id;
            }
            $children = Region::where('parent_id', $parent_id)->withCount('children')->append(['leaf'])->select();
            $children = $children ? $children->toArray() : [];
            // 将这些子地区存储在以 parent_id 为键的数组中
            $res[$parent_id] = $children;
        }

        $tree = $this->buildTree($res, $top_parent_id);
        return $tree;
    }

    /**
     * 获取所有地区树
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAllRegionTree(): array
    {
        $data = cache('regionList');
        if ($data === null || $data === false) {
            $res = array();
            $res = Region::where('level', '>', 0)->field('region_id,region_name,parent_id,level')->select()->toArray();
            $data = $this->getLevelTree($res, 0, 2);
            $this->removeKeys($data, ['level', 'parent_id']);
            cache('regionList', $data, 86400 * 100, 'data');
        }
        return $data;
    }

    /**
     * 递归构建树结构
     * @param $data
     * @param $parentId
     * @param $level
     * @return array
     */
    public function getLevelTree($data, $parentId = 0, $level = 2): array
    {
        $tree = [];
        foreach ($data as $item) {
            if (($item['parent_id'] == $parentId || $parentId == 0) && $item['level'] == $level) {
                $children = $this->getLevelTree($data, $item['region_id'], $level + 1);
                if ($children) {
                    $item['children'] = $children;
                }
                $tree[] = $item;
            }
        }
        return $tree;
    }

    /**
     * 递归删除数组中的指定键
     * @param $array
     * @param $keysToRemove
     * @return void
     */
    public function removeKeys(&$array, $keysToRemove): void
    {
        foreach ($array as $key => &$value) {
            // 若当前键为需移除的键，则删除它
            if (in_array($key, $keysToRemove)) {
                unset($array[$key]);
            }
            // 若当前值为数组，递归调用函数
            if (is_array($value)) {
                $this->removeKeys($value, $keysToRemove);
            }
        }
    }

    /**
     * 构建一个辅助函数，递归地构建树结构
     * @param $res
     * @param $parentId
     * @return array
     */
    public function buildTree(&$res, $parentId = 0): array
    {
        $branch = array();

        // 通过 parent_id 查找所有子项
        if (isset($res[$parentId])) {
            foreach ($res[$parentId] as $element) {
                // 如果当前子项有自己的子项，递归调用此函数构建子树
                $children = $this->buildTree($res, $element['region_id']);
                if ($children) {
                    $element['children'] = $children;
                } else {
                    $element['children'] = [];
                }
                $branch[] = $element;
            }
        }
        return $branch;
    }

    /**
     * 获得所有省份数据
     * @return array
     */
    public function getProvinceList(): array
    {
        $list = Region::where('level', 2)->field(['region_id', 'region_name'])->select();
        return $list->toArray();
    }

}
