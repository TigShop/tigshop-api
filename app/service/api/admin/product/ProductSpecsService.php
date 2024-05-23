<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 商品规格
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\product;

use app\model\product\ProductSpecs;
use app\service\api\admin\BaseService;
use app\validate\product\ProductSpecsValidate;
use exceptions\ApiException;
use log\AdminLog;

/**
 * 商品规格服务类
 */
class ProductSpecsService extends BaseService
{
    protected ProductSpecs $productSpecsModel;
    protected ProductSpecsValidate $productSpecsValidate;

    public function __construct(ProductSpecs $productSpecsModel)
    {
        $this->productSpecsModel = $productSpecsModel;
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
        $query = $this->productSpecsModel->query();
        // 处理筛选条件

        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->where('spec_value', 'like', '%' . $filter['keyword'] . '%');
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
     * @return array
     * @throws ApiException
     */
    public function getDetail(int $id): array
    {
        $result = $this->productSpecsModel->where('spec_id', $id)->find();
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
        return $this->productSpecsModel::where('spec_id', $id)->value('spec_value');
    }

    /**
     * 执行商品规格添加或更新
     *
     * @param int $id
     * @param array $data
     * @param bool $isAdd
     * @return int|bool
     * @throws ApiException
     */
    public function updateProductSpecs(int $id, array $data, bool $isAdd = false): int | bool
    {
        validate(ProductSpecsValidate::class)->only(array_keys($data))->check($data);
        if ($isAdd) {
            $result = $this->productSpecsModel->save($data);
            AdminLog::add('新增商品规格:' . $data['spec_value']);
            return $this->productSpecsModel->getKey();
        } else {
            if (!$id) {
                throw new ApiException('#id错误');
            }
            $result = $this->productSpecsModel->where('spec_id', $id)->save($data);
            AdminLog::add('更新商品规格:' . $this->getName($id));

            return $result !== false;
        }
    }

    /**
     * 删除商品规格
     * @param int $id
     * @return bool
     * @throws ApiException
     */
    public function deleteProductSpecs(int $id): bool
    {
        if (!$id) {
            throw new ApiException('#id错误');
        }
        $result = $this->productSpecsModel::destroy($id);

        if ($result) {
            AdminLog::add('删除商品规格:' . $this->getName($id));
        }

        return $result !== false;
    }

    /**
     * 获取规格
     * @param int $product_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getSpecsList(int $product_id): array
    {
        $result = $this->productSpecsModel->where('product_id', $product_id)->select()->toArray();
        foreach ($result as $key => $value) {
            // $result[$key]['spec_values'] = explode('|', $value['spec_value']);
        }
        return $result;
    }

    /**
     * 处理规格数据
     * @param int $product_id
     * @param array $product_list
     * @return void
     * @throws \Exception
     */
    public function dealProductSpec(int $product_id, array $product_list = []): void
    {
        $data = [];
        foreach ($product_list as $key => $value) {

            $row['spec_stock'] = intval($value['spec_stock']);
            $row['spec_sn'] = $value['spec_sn'];
            $row['spec_tsn'] = $value['spec_tsn'];
            $row['spec_price'] = floatval($value['spec_price']);
            $arr = array();
            foreach ($value['attrs'] as $k => $val) {
                $row['spec_data'][$k]['name'] = $val['attr_name'];
                $row['spec_data'][$k]['value'] = $val['attr_value'];
                $arr[] = implode(':', $val);
            }
            $row['spec_value'] = implode('|', $arr);
            $exist_spec_id = $this->productSpecsModel->where('product_id', $product_id)->where('spec_value', $row['spec_value'])->value('spec_id');
            if ($exist_spec_id) {
                $row['spec_id'] = $exist_spec_id;
            } else {
                $row['product_id'] = $product_id;
            }
            $data[] = $row;
        }
        $result = $this->productSpecsModel->saveAll($data);
        $spec_ids = $result->column('spec_id');
        // 删除不在数据里的spec
        $this->productSpecsModel->where('product_id', $product_id)->whereNotIn('spec_id', $spec_ids)->delete();
    }
}
