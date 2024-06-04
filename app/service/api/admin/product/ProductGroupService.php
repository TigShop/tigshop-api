<?php

namespace app\service\api\admin\product;

use app\model\product\ProductGroup;
use app\service\api\admin\BaseService;
use exceptions\ApiException;


/**
 * 商品分组服务类
 */
class ProductGroupService extends BaseService
{
    protected ProductGroup $productGroup;

    public function __construct(ProductGroup $productGroup)
    {
        $this->productGroup = $productGroup;
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
        $query = $this->productGroup->query();
        // 处理筛选条件

        if (!empty($filter['keyword'])) {
            $query->where('product_group_name', 'like', '%' . $filter['keyword'] . '%');
        }
        if (isset($filter['shop_id'])) {
            $query->where('shop_id', $filter['shop_id']);
        }

        if (!empty($filter['sort_field']) && !empty($filter['sort_order'])) {
            $query->order($filter['sort_field'], $filter['sort_order']);
        }
        return $query;
    }

    /**
     * 获取详情
     *
     * @param int $id
     * @return ProductGroup |null
     * @throws ApiException
     */
    public function getDetail(int $id): ProductGroup|null
    {
        return $this->productGroup->where('product_group_id', $id)->find();
    }

    /**
     * 新增
     * @param array $data
     * @return \think\Model|ProductGroup
     */
    public function create(array $data): \think\Model|ProductGroup
    {
        return ProductGroup::create($data);
    }

    /**
     * 执行商品分组更新
     *
     * @param int $id
     * @param array $data
     * @return ProductGroup
     * @throws ApiException
     */
    public function edit(int $id, array $data): ProductGroup
    {
        $item = $this->getDetail($id);
        $item->save($data);
        return $item;
    }

    /**
     * 删除商品分组
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $result = $this->productGroup::destroy($id);
        return $result !== false;
    }

}
