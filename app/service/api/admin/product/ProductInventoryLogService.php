<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 商品库存日志
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\product;

use app\model\product\ProductInventoryLog;
use app\service\api\admin\BaseService;
use app\validate\product\ProductInventoryLogValidate;
use exceptions\ApiException;

/**
 * 商品库存日志服务类
 */
class ProductInventoryLogService extends BaseService
{
    protected ProductInventoryLog $productInventoryLogModel;
    protected ProductInventoryLogValidate $productInventoryLogValidate;

    public function __construct(ProductInventoryLog $productInventoryLogModel)
    {
        $this->productInventoryLogModel = $productInventoryLogModel;
    }

    /**
     * 获取筛选结果
     *
     * @param array $filter
     * @return array
     */
    public function getFilterResult(array $filter): array
    {
        $query = $this->filterQuery($filter)->with(["product"]);
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
        $query = $this->productInventoryLogModel->query();
        // 处理筛选条件
        if (isset($filter['keyword']) && !empty($filter["keyword"])) {
            $query->productName($filter["keyword"]);
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
        $result = $this->productInventoryLogModel->where('log_id', $id)->find();

        if (!$result) {
            throw new ApiException('商品库存日志不存在');
        }

        return $result->toArray();
    }

    /**
     * 执行商品库存日志添加或更新
     *
     * @param int $id
     * @param array $data
     * @param bool $isAdd
     * @return int|bool
     * @throws ApiException
     */
    public function updateProductInventoryLog(int $id, array $data, bool $isAdd = false): int | bool
    {
        validate(ProductInventoryLogValidate::class)->only(array_keys($data))->check($data);
        if ($isAdd) {
            $result = $this->productInventoryLogModel->save($data);
            return $this->productInventoryLogModel->getKey();
        } else {
            if (!$id) {
                throw new ApiException('#id错误');
            }
            $result = $this->productInventoryLogModel->where('log_id', $id)->save($data);

            return $result !== false;
        }
    }

    /**
     * 删除商品库存日志
     *
     * @param int $id
     * @return bool
     */
    public function deleteProductInventoryLog(int $id): bool
    {
        if (!$id) {
            throw new ApiException('#id错误');
        }
        $result = $this->productInventoryLogModel::destroy($id);

        return $result !== false;
    }
}
