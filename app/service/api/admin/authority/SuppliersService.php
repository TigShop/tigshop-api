<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 供应商
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\authority;

use app\model\authority\Suppliers;
use app\service\core\BaseService;
use exceptions\ApiException;

/**
 * 供应商服务类
 */
class SuppliersService extends BaseService
{
    protected Suppliers $suppliersModel;

    public function __construct(Suppliers $suppliersModel)
    {
        $this->suppliersModel = $suppliersModel;
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
        $query = $this->suppliersModel->query();
        // 处理筛选条件

        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->where('suppliers_name', 'like', '%' . $filter['keyword'] . '%');
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
     * @return Suppliers
     * @throws ApiException
     */
    public function getDetail(int $id): Suppliers
    {
        $result = $this->suppliersModel->where('suppliers_id', $id)->append(["regions"])->find();

        if (!$result) {
            throw new ApiException('供应商不存在');
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
        return $this->suppliersModel::where('suppliers_id', $id)->value('suppliers_name');
    }

    /**
     * 获取添加/更新的通用数据
     * @param array $data
     * @return array
     */
    public function getCommunalData(array $data):array
    {
        $province = $city = $district = '';
        if(!empty($data['regions'])){
            // 获取省市区
            list($province, $city, $district) = $data['regions'];
        }
        $arr = [
            'suppliers_name' => $data['suppliers_name'],
            'suppliers_desc' => $data['suppliers_desc'],
            'is_check' => $data['is_check'],
            'province' => $province,
            'city' => $city,
            'district' => $district,
            'contact_name' => $data['contact_name'],
            'contact_phone' => $data['contact_phone'],
            'contact_address' => $data['contact_address'],
        ];
        return $arr;
    }

    /**
     * 执行供应商添加
     * @param array $data
     * @return int
     * @throws ApiException
     */
    public function createSuppliers(array $data):int
    {
        $arr = $this->getCommunalData($data);
        $result = $this->suppliersModel->create($arr);
        return $result->suppliers_id;
    }

    /**
     * 执行供应商更新
     *
     * @param int $id
     * @param array $data
     * @return bool
     * @throws ApiException
     */
    public function updateSuppliers(int $id, array $data):bool
    {
        $arr = $this->getCommunalData($data);
        if (!$id) {
            throw new ApiException('#id错误');
        }
        $result = $this->suppliersModel->where('suppliers_id', $id)->save($arr);
        return $result !== false;
    }

    /**
     * 删除供应商
     *
     * @param int $id
     * @return bool
     */
    public function deleteSuppliers(int $id): bool
    {
        if (!$id) {
            throw new ApiException('#id错误');
        }
        $result = $this->suppliersModel::destroy($id);
        return $result !== false;
    }

    /**
     * 获取供应商列表
     * @return array
     */
    public function getSuppliersList():array
    {
        $result = $this->suppliersModel->field('suppliers_name,suppliers_id')->where('is_check', 1)->select();
        return $result->toArray();
    }

    /**
     * 更新单个字段
     *
     * @param int $id
     * @param array $data
     * @return int|bool
     * @throws ApiException
     */
    public function updateSuppliersField(int $id, array $data):bool
    {
        if (!$id) {
            throw new ApiException('#id错误');
        }
        $result = Suppliers::where('suppliers_id', $id)->save($data);
        return $result !== false;
    }
}
