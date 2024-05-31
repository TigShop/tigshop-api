<?php

namespace app\service\api\admin\authority;

use app\model\authority\Authority;
use app\service\api\admin\BaseService;
use exceptions\ApiException;
use log\AdminLog;

/**
 * 商品权限服务类
 */
class AuthorityService extends BaseService
{
    protected Authority $authorityModel;

    public function __construct(Authority $authorityModel)
    {
        $this->authorityModel = $authorityModel;
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
        $result = $query->field('c.*, COUNT(s.authority_id) AS has_children')
            ->leftJoin('authority s', 'c.authority_id = s.parent_id')
            ->group('c.authority_id')->page($filter['page'], $filter['size'])->select();
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
        $query = $this->authorityModel->query()->alias('c');
        // 处理筛选条件

        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->where('c.authority_name', 'like', '%' . $filter['keyword'] . '%');
        }
        if (isset($filter['admin_type']) && $filter['admin_type'] != -1) {
            $query->whereIn('c.admin_type', explode(',', $filter['admin_type']));
        }
        $query->where('c.parent_id', $filter['parent_id']);

        if (isset($filter['sort_field'], $filter['sort_order']) && !empty($filter['sort_field']) && !empty($filter['sort_order'])) {
            $query->order($filter['sort_field'], $filter['sort_order']);
        }
        return $query;
    }

    /**
     * 获取详情
     *
     * @param int $id
     * @throws ApiException
     */
    public function getDetail(int $id): Authority
    {
        $result = $this->authorityModel->where('authority_id', $id)->find();

        if (!$result) {
            throw new ApiException(/** LANG */'权限不存在');
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
        return $this->authorityModel::where('authority_id', $id)->value('authority_name');
    }


    /**
     * 新增
     * @param array $data
     * @return int
     * @throws ApiException
     */
    public function createAuthority(array $data):int
    {
        $this->authorityModel->save($data);
        AdminLog::add('新增权限:' . $data['authority_name']);
        return $this->authorityModel->getKey();
    }

    /**
     * 更新
     *
     * @param int $id
     * @param array $data
     * @param bool $isAdd
     * @return bool
     * @throws ApiException
     */
    public function updateAuthority(int $id, array $data):bool
    {
        $result = $this->authorityModel->where('authority_id', $id)->save($data);
        AdminLog::add('更新权限:' . $this->getName($id));
        return $result !== false;
    }

    /**
     * 检测parent_id数据问题
     * @param array $data
     * @param int $id
     * @return void
     * @throws ApiException
     */
    protected function checkParentId(array $data, int $id = 0): void
    {
        if (isset($data['parent_id'])) {
            /* 判断上级目录是否合法 */
            $children = $this->authorityAllChildIds($id,$data["auth_list"]);     // 获得当前权限的所有下级权限
            unset($children[0]);
            if ($id) {
                if (in_array($data['parent_id'], $children)) {
                    /* 选定的父类是当前权限或当前权限的下级权限 */
                    throw new ApiException(/** LANG */'所选择的上级权限不能是当前权限或者当前权限的下级权限');
                }
                if ($id == $data['parent_id']) {
                    /* 选定的父类是当前权限或当前权限的下级权限 */
                    throw new ApiException(/** LANG */'所选择的上级权限不能是当前权限');
                }
            }
        }
    }

    /**
     * 删除权限
     *
     * @param int $id
     * @return bool
     */
    public function deleteAuthority(int $id): bool
    {
        $result = $this->authorityModel::destroy($id);
        if ($result) {
            AdminLog::add('删除权限');
        }
        return $result !== false;
    }


    /**
     * 获取所有权限列表 新方法
     * @param int $authority_id 获取该权限id下的所有权限（不含该权限）
     * @param bool $return_ids 是否返回权限id列表
     * @return array
     */
    public function authorityList(
        int $authority_id = 0,
        int $type = 0,
        array $auth_list = [],
        string $adminType = 'admin'
    ): array
    {
        $cat_list = Authority::alias('c')->where('admin_type',
            $adminType)->field('c.authority_id, c.is_show,c.authority_sn,c.authority_name, c.parent_id, c.parent_id,c.authority_ico,c.route_link,c.child_auth')
            ->order('c.parent_id, c.sort_order ASC, c.authority_id ASC')->select();
        $cat_list = $cat_list ? $cat_list->toArray() : [];
        $res = $this->xmsbGetDataTree($cat_list, $authority_id);

        if ($type) {
            $res = $this->flattenTree($res);
            foreach ($res as $k=>&$auth){

                if(!empty($auth['children']) && is_array($auth['children']))
                {
                    foreach ($auth['children'] as $k2=>$childrens)
                    {
//                        if(!empty($childrens['child_auth']) && is_array($childrens['child_auth'])){
//                            $insert_array = [];
//                            $first_array = array_splice($auth['children'],0,$k2+1);
//                            foreach ($childrens['child_auth'] as $k3=>$children)
//                            {
//                                $insert_array[] = [
//                                    'authority_name' => $children['auth_name'],
//                                    'authority_sn' => $children['auth_sn']
//                                ];
//                            }
//                            $auth['children'] = array_merge($first_array,$insert_array,$auth['children']);
//                        }
                    }
                }
            }
        }

        // 处理当前角色权限
        if (!empty($auth_list) && !in_array('all', $auth_list)) {
            foreach ($res as $key => $value) {
                if (isset($value['children'])) {
                    $children = $value['children'];
                    $filtered_children = array_filter($children, function ($child) use ($auth_list) {
                        return in_array($child['authority_sn'], $auth_list);
                    });
                    if (count($filtered_children) === 0 && !in_array($value['authority_sn'], $auth_list)) {
                        unset($res[$key]);
                    }
                    foreach ($filtered_children as &$child) {
                        if (!empty($child['child_auth']) && is_array($child['child_auth'])) {
                            foreach ($child['child_auth'] as $k3 => $children) {
                                if (!in_array($children, $auth_list)) {
                                    unset($child['child_auth'][$k3]);
                                }
                            }
                        }
                    }
                    $res[$key]['children'] = $filtered_children;

                } else {
                    if (!in_array($value['authority_sn'], $auth_list)) {
                        unset($res[$key]);
                    }
                }
            }
        }
        //重构数据
        $main_menu = [];
        foreach ($res as $key => $value) {
            $main_menu[] = $value;
        }
        return (array)$main_menu;


    }

    /**
     * 修改树结构
     * @param $authorityLists
     * @return array
     */
    public function flattenTree(array $authorityLists): array
    {
        $flattenedList = [];
        foreach ($authorityLists as $key => $authority) {
            if (isset($authority['children'])) {
                foreach ($authority['children'] as $k => $v) {
                    if (isset($v['children'])) {
                        $children = $v['children'];
                        unset($authority['children'][$k]["children"]);
                        $authority["children"] = array_merge($authority['children'], $children);
                    }
                }
            }
            $flattenedList[$key] = $authority;
        }
        return $flattenedList;
    }


    /**
     * 获取指定权限id下的所有子权限id列表
     * @param int $authority_id 权限id
     * @return array
     */
    public function authorityAllChildIds(int $authority_id = 0, array $auth_list = []): array
    {
        $cat_list = $this->authorityList($authority_id,0,$auth_list);
        $ids = [$authority_id];

        $this->getChildrenIds($cat_list, $ids);

        return $ids;
    }

    public function getChildrenIds($category, &$ids)
    {
        if (!empty($category["children"])) {
            foreach ($category["children"] as $child) {
                $ids[] = $child['authority_id'];
                $this->getChildrenIds($child, $ids);
            }
        }
    }

    /**
     * 无限级权限函数
     * @param array $arr 查询出的数据
     * @param int $first_parent 根节点主键值
     * @return array
     */
    public function xmsbGetDataTree(array $arr, int $first_parent = 0): array
    {
        $tree = ['authority_id' => 0, 'parent_id' => 0];
        $tmpMap = [$first_parent => &$tree];
        foreach ($arr as $rk => $rv) {
            $tmpMap[$rv['authority_id']] = $rv;
            $parentObj = &$tmpMap[$rv['parent_id']];
            if (!isset($parentObj['children'])) {
                $parentObj['children'] = [];
            }
            $parentObj['children'][] = &$tmpMap[$rv['authority_id']];
        }
        if (!isset($tree['children'])) {
            return (array)$tree;
        }
        return (array)$tree['children'];
    }

    /**
     * 检查权限
     *
     * @param string $authority_sn
     * @return bool
     */
    public function checkAuthor(string $authority_sn, int $shop_id = 0, array $auth_list = []): bool
    {
        if ($shop_id == 0) {
            if (in_array('all', $auth_list)) {
                return true;
            }
            if (!in_array($authority_sn, $auth_list)) {
                throw new ApiException(/** LANG */'无此操作权限');
            }
        } else {
            // 店铺
        }
        return true;
    }

    /**
     * 更新单个字段
     *
     * @param int $id
     * @param array $data
     * @return int|bool
     * @throws ApiException
     */
    public function updateAuthorityField(int $id, array $data)
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = Authority::where('authority_id', $id)->save($data);
        AdminLog::add('更新权限:' . $this->getName($id));
        return $result !== false;
    }

    /**
     * 一键直达 -- 权限名称检索
     * @param string $keyword
     * @return array
     */
    public function getAuthorityList(string $keyword): array
    {
        $result = [];
        if (!empty($keyword)) {
            $result = Authority::where('authority_name', 'like', '%' . $keyword . '%')
                ->where('is_show', 1)
                ->order('sort_order', 'desc')
                ->field("authority_name,route_link")
                ->select()->toArray();
        }
        return $result;
    }
}
