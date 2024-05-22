<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 商品服务
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\product;

use app\common\exceptions\ApiException;
use app\common\log\AdminLog;
use app\model\product\ProductServices;
use app\service\api\admin\BaseService;
use app\validate\product\ProductServicesValidate;

/**
 * 商品服务服务类
 */
class ProductServicesService extends BaseService
{
    protected ProductServices $productServicesModel;
    protected ProductServicesValidate $productServicesValidate;

    public function __construct(ProductServices $productServicesModel)
    {
        $this->productServicesModel = $productServicesModel;
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
        $query = $this->productServicesModel->query();
        // 处理筛选条件

        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->where('product_service_name', 'like', '%' . $filter['keyword'] . '%');
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
     * @return array
     * @throws ApiException
     */
    public function getDetail(int $id): array
    {
        $result = $this->productServicesModel->where('product_service_id', $id)->find();

        if (!$result) {
            throw new ApiException('商品服务不存在');
        }

        return $result->toArray();
    }

    /**
     * 获取名称
     *
     * @param int $id
     * @return string|null
     */
    public function getName(int $id): ?string
    {
        return $this->productServicesModel::where('product_service_id', $id)->value('product_service_name');
    }

    /**
     * 执行商品服务添加或更新
     *
     * @param int $id
     * @param array $data
     * @param bool $isAdd
     * @return int|bool
     * @throws ApiException
     */
    public function updateProductServices(int $id, array $data, bool $isAdd = false)
    {
        validate(ProductServicesValidate::class)->only(array_keys($data))->check($data);
        if ($isAdd) {
            $result = $this->productServicesModel->save($data);
            AdminLog::add('新增商品服务:' . $data['product_service_name']);
            return $this->productServicesModel->getKey();
        } else {
            if (!$id) {
                throw new ApiException('#id错误');
            }
            $result = $this->productServicesModel->where('product_service_id', $id)->save($data);
            AdminLog::add('更新商品服务:' . $this->getName($id));

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
    public function updateProductServicesField(int $id, array $data)
    {
        validate(ProductServicesValidate::class)->only(array_keys($data))->check($data);
        if (!$id) {
            throw new ApiException('#id错误');
        }
        $result = $this->productServicesModel::where('product_service_id', $id)->save($data);
        AdminLog::add('更新商品服务:' . $this->getName($id));
        return $result !== false;
    }

    /**
     * 删除商品服务
     *
     * @param int $id
     * @return bool
     */
    public function deleteProductServices(int $id): bool
    {
        if (!$id) {
            throw new ApiException('#id错误');
        }
        $get_name = $this->getName($id);
        $result = $this->productServicesModel::destroy($id);

        if ($result) {
            AdminLog::add('删除商品服务:' . $get_name);
        }

        return $result !== false;
    }

    public function getProductService(): array
    {
        $result = $this->productServicesModel->field('product_service_id,product_service_name')->select();
        return $result->toArray();
    }
}
