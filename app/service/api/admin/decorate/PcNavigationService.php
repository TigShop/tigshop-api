<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- PC导航栏
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\decorate;

use app\model\decorate\PcNavigation;
use app\service\api\admin\BaseService;
use exceptions\ApiException;

/**
 * PC导航栏服务类
 */
class PcNavigationService extends BaseService
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
        $query = $this->filterQuery($filter)->append(["type_name"]);
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
        $query = PcNavigation::query();
        // 处理筛选条件

        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->where('title', 'like', '%' . $filter['keyword'] . '%');
        }

        if (isset($filter['type']) && $filter["type"] > 0) {
            $query->where('type', $filter['type']);
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
     * @return PcNavigation
     * @throws ApiException
     */
    public function getDetail(int $id): PcNavigation
    {
        $result = PcNavigation::where('id', $id)->append(["type_name"])->find();

        if (!$result) {
            throw new ApiException(/** LANG */'PC导航栏不存在');
        }

        return $result;
    }

    /**
     * 获取所有导航栏
     * @return array
     */
    public function getAllNav(): array
    {
        $data = [
            'main_nav' => [],
            'top_bar_nav' => [],
            'bottom_nav' => [],
            'sidebar_nav' => [],
        ];
        $nav = $this->getParentNav();
        foreach ($nav as $key => $value) {
            if ($value['type'] == PcNavigation::TYPE_MAIN) {
                $data['main_nav'][] = $value;
            } elseif ($value['type'] == PcNavigation::TYPE_TOP_BAR) {
                $data['top_bar_nav'][] = $value;
            } elseif ($value['type'] == PcNavigation::TYPE_BOTTOM) {
                $data['bottom_nav'][] = $value;
            } elseif ($value['type'] == PcNavigation::TYPE_SIDEBAR) {
                $data['sidebar_nav'][] = $value;
            }
        }

        return $data;
    }

    /**
     * 获取树形导航
     * @param int $type
     * @param int $parent_id
     * @return array
     */
    public function getParentNav(int $type = 0, int $parent_id = 0): array
    {
        $nav_list = PcNavigation::when($type > 0, function ($query) use ($type) {
            return $query->where('type', $type);
        })->where("is_show", 1)->order('parent_id', 'asc')->order("sort_order", "asc")->select()->toArray();
        $res = [];

        if (!empty($nav_list)) {
            $res = $this->xmsbGetDataTree($nav_list, $parent_id);
            // 保留二层分类
            $res = $this->flattenTree($res);
        }
        return (array) $res;
    }

    /**
     * 获取数据树
     * @param array $arr
     * @param int $first_parent
     * @return array
     */
    public function xmsbGetDataTree(array $arr, int $first_parent = 0, $data = []): array
    {
        $tree = ['id' => 0, 'parent_id' => 0];
        $tmpMap = [$first_parent => &$tree];
        foreach ($arr as $rk => $rv) {
            $tmpMap[$rv['id']] = $rv;
            $parentObj = &$tmpMap[$rv['parent_id']];
            if (!isset($parentObj['children'])) {
                $parentObj['children'] = [];
            }
            $parentObj['children'][] = &$tmpMap[$rv['id']];
        }
        return (array) $tree['children'];
    }


    /**
     * 获取二层分类
     * @param array $nav_list
     * @return array
     */
    public function flattenTree(array $nav_list): array
    {
        $flattenedList = [];
        foreach ($nav_list as $key => $nav) {
            if (isset($nav['children'])) {
                foreach ($nav['children'] as $k => $v) {
                    if (isset($v['children'])) {
                        unset($nav['children'][$k]["children"]);
                    }
                }
            }
            $flattenedList[$key] = $nav;
        }
        return $flattenedList;
    }

    /**
     * 获取基础链接
     * @return array
     */
    public function getBaseLink(): array
    {
        $data = [
            ["name" => "商城首页", "app_link" => "home/index", "link" => "/"],
            ["name" => "分类页面（仅分类）", "app_link" => "catalog/index", "link" => "catalog/"],
            ["name" => "分类页面（带商品滑动分类）", "app_link" => "overseas/index", "link" => "overseas/"],
            ["name" => "限时秒杀", "app_link" => "qianggou/index", "link" => "qianggou/"],
            ["name" => "拼团活动", "app_link" => "pin_list/index", "link" => "pin/list/"],
            ["name" => "购物车", "app_link" => "cart/index", "link" => "cart/"],
            ["name" => "搜索页面", "app_link" => "product_search/index", "link" => "search/"],
            ["name" => "新品推荐", "app_link" => "product_search/index?intro=new", "link" => "search/?intro=new"],
            ["name" => "热销推荐", "app_link" => "product_search/index?intro=hot", "link" => "search/?intro=hot"],
            ["name" => "精品推荐", "app_link" => "product_search/index?intro=best", "link" => "search/?intro=best"],

            ["name" => "资讯中心", "app_link" => "article/news", "link" => "article/news/"],
            ["name" => "会员首页", "app_link" => "member/index", "link" => "member/"],
            ["name" => "我的优惠券", "app_link" => "member/bonus/index", "link" => "member/bonus/list/"],
            ["name" => "我的订单", "app_link" => "member/order_list/index", "link" => "member/order/list/"],
            ["name" => "待付款订单", "app_link" => "member/order_list/index?order_type=2", "link" => "member/order/list/?order_type=2"],
            ["name" => "待评价订单", "app_link" => "member/order_list/index?com_type=all&com_status=1", "link" => "member/order/comment/?com_type=all&com_status=1"],
            ["name" => "收货地址", "app_link" => "member/address_list/index", "link" => "member/address/list/"],
            ["name" => "退换货", "app_link" => "member/return_list/index", "link" => "member/return/list/"],
            ["name" => "账户余额", "app_link" => "member/account_detail/index", "link" => "member/account/detail/"],
            ["name" => "我的积分", "app_link" => "member/point_detail/index", "link" => "member/point/detail/"],
            ["name" => "收藏商品", "app_link" => "member/collection_list/index", "link" => "member/collection/list/"],
            ["name" => "留言咨询", "app_link" => "member/message/index", "link" => "member/message/list/"],
            ["name" => "站内消息", "app_link" => "member/user_message/index", "link" => "user_message/"],
            ["name" => "推荐返利", "app_link" => "member/affiliate/index", "link" => "member/affiliate/"],
            ["name" => "发票管理", "app_link" => "member/index", "link" => "order/finished_order/"],
            ["name" => "账号信息", "app_link" => "member/profile/index", "link" => "member/profile/"],
            ["name" => "浏览历史", "app_link" => "member/history/index", "link" => "member/history/list/"],

            ["name" => "我的砍一砍", "app_link" => "user/index", "link" => "member/bargain/list/"],
            ["name" => "我的拼团", "app_link" => "user/index", "link" => "member/pin/list/"],
            ["name" => "我的积分抽奖", "app_link" => "user/index", "link" => "member/lottery/my/"],
        ];

        return $data;
    }

    /**
     * 执行PC导航栏添加
     * @param array $data
     * @return int
     */
    public function createNavigation(array $data): int
    {
        $max_sort_order = PcNavigation::where('type', $data['type'])->max("sort_order");
        $data["sort_order"] = empty($data["sort_order"]) ? $max_sort_order + 1 : $data["sort_order"];
        $result = PcNavigation::create($data);
        return $result->getKey();
    }

    /**
     * 执行PC导航栏更新
     *
     * @param int $id
     * @param array $data
     * @return bool
     * @throws ApiException
     */
    public function updateNavigation(int $id, array $data): bool
    {
        $max_sort_order = PcNavigation::where('type', $data['type'])->max("sort_order");
        $data["sort_order"] = empty($data["sort_order"]) ? $max_sort_order + 1 : $data["sort_order"];
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = PcNavigation::where('id', $id)->save($data);
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
    public function updateNavigationField(int $id, array $data)
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = PcNavigation::where('id', $id)->save($data);
        return $result !== false;
    }

    /**
     * 删除PC导航栏
     *
     * @param int $id
     * @return bool
     */
    public function deleteNavigation(int $id): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = PcNavigation::destroy($id);
        return $result !== false;
    }
}
