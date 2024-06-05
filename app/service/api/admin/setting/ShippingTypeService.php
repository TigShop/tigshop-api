<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 配送类型
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\setting;

use app\model\setting\ShippingType;
use app\service\core\BaseService;
use exceptions\ApiException;
use log\AdminLog;

/**
 * 配送类型服务类
 */
class ShippingTypeService extends BaseService
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
        $query = $this->filterQuery($filter)->with('logisticsCompany');
        if (isset($filter['paging']) && $filter['paging']) {
            $query->page($filter['page'], $filter['size']);
        }
        $result = $query->select();
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
        $query = ShippingType::query();
        // 处理筛选条件
        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->where('shipping_type_name', 'like', '%' . $filter['keyword'] . '%');
        }

        if (isset($filter['sort_field'], $filter['sort_order']) && !empty($filter['sort_field']) && !empty($filter['sort_order'])) {
            $query->order($filter['sort_field'], $filter['sort_order']);
        }
        return $query;
    }
    public function getAllShippingType(array $filter = []): array
    {
        $result = $this->filterQuery($filter)->select();
        return $result->toArray();
    }

    /**
     * 获取详情
     *
     * @param int $id
     * @return ShippingType
     * @throws ApiException
     */
    public function getDetail(int $id): ShippingType
    {
        $result = ShippingType::with('logisticsCompany')->where('shipping_type_id', $id)->find();

        if (!$result) {
            throw new ApiException(/** LANG */'配送类型不存在');
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
        return ShippingType::where('shipping_type_id', $id)->value('shipping_type_name');
    }

    /**
     * 添加配送类型
     * @param array $data
     * @return int
     */
    public function createShippingType(array $data): int
    {
        $result = ShippingType::create($data);
        AdminLog::add('新增配送类型:' . $data['shipping_type_name']);
        return $result->getKey();
    }

    /**
     * 执行配送类型更新
     * @param int $id
     * @param array $data
     * @return bool
     * @throws ApiException
     */
    public function updateShippingType(int $id, array $data): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = ShippingType::where('shipping_type_id', $id)->save($data);
        AdminLog::add('更新配送类型:' . $this->getName($id));

        return $result !== false;
    }

    /**
     * 删除配送类型
     *
     * @param int $id
     * @return bool
     */
    public function deleteShippingType(int $id): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $get_name = $this->getName($id);
        $result = ShippingType::destroy($id);

        if ($result) {
            AdminLog::add('删除配送类型:' . $get_name);
        }

        return $result !== false;
    }
}
