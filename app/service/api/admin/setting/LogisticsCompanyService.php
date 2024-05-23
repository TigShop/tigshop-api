<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 物流公司
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\setting;

use app\model\setting\LogisticsCompany;
use app\service\api\admin\BaseService;
use exceptions\ApiException;
use log\AdminLog;

/**
 * 物流公司服务类
 */
class LogisticsCompanyService extends BaseService
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
        if ($filter['paging']) {
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
        $query = LogisticsCompany::query();
        // 处理筛选条件
        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->where('logistics_name', 'like', '%' . $filter['keyword'] . '%');
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
     * @return LogisticsCompany
     * @throws ApiException
     */
    public function getDetail(int $id): LogisticsCompany
    {
        $result = LogisticsCompany::where('logistics_id', $id)->find();

        if (!$result) {
            throw new ApiException('物流公司不存在');
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
        return LogisticsCompany::where('logistics_id', $id)->value('logistics_name');
    }

    /**
     * 创建物流公司
     * @param array $data
     * @return int
     */
    public function createLogisticsCompany(array $data): int
    {
        $result = LogisticsCompany::create($data);
        AdminLog::add('新增物流公司:' . $data['logistics_name']);
        return $result->getKey();
    }


    /**
     * 执行物流公司更新
     *
     * @param int $id
     * @param array $data
     * @return bool
     * @throws ApiException
     */
    public function updateLogisticsCompany(int $id, array $data): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = LogisticsCompany::where('logistics_id', $id)->save($data);
        AdminLog::add('更新物流公司:' . $this->getName($id));
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
    public function updateLogisticsCompanyField(int $id, array $data)
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = LogisticsCompany::where('logistics_id', $id)->save($data);
        AdminLog::add('更新物流公司:' . $this->getName($id));
        return $result !== false;
    }

    /**
     * 删除物流公司
     *
     * @param int $id
     * @return bool
     */
    public function deleteLogisticsCompany(int $id): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $get_name = $this->getName($id);
        $result = LogisticsCompany::destroy($id);

        if ($result) {
            AdminLog::add('删除物流公司:' . $get_name);
        }

        return $result !== false;
    }
}
