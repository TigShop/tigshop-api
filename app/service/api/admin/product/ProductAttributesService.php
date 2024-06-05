<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 商品属性
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\product;

use app\model\product\ProductAttributes;
use app\service\core\BaseService;
use app\validate\product\ProductAttributesValidate;
use exceptions\ApiException;
use log\AdminLog;

/**
 * 商品属性服务类
 */
class ProductAttributesService extends BaseService
{
    protected ProductAttributes $productAttributesModel;
    protected ProductAttributesValidate $productAttributesValidate;

    public function __construct(ProductAttributes $productAttributesModel)
    {
        $this->productAttributesModel = $productAttributesModel;
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
        $query = $this->productAttributesModel->query();
        // 处理筛选条件

        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->where('attr_name', 'like', '%' . $filter['keyword'] . '%');
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
        $result = $this->productAttributesModel->where('attributes_id', $id)->find();

        if (!$result) {
            throw new ApiException('商品属性不存在');
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
        return $this->productAttributesModel::where('attributes_id', $id)->value('attr_name');
    }

    /**
     * 执行商品属性添加或更新
     * @param int $id
     * @param array $data
     * @param bool $isAdd
     * @return bool
     * @throws ApiException
     */
    public function updateProductAttributes(int $id, array $data, bool $isAdd = false): bool
    {
        validate(ProductAttributesValidate::class)->only(array_keys($data))->check($data);
        if ($isAdd) {
            $result = $this->productAttributesModel->save($data);
            AdminLog::add('新增商品属性:' . $data['attr_name']);
            return $this->productAttributesModel->getKey();
        } else {
            if (!$id) {
                throw new ApiException('#id错误');
            }
            $result = $this->productAttributesModel->where('attributes_id', $id)->save($data);
            AdminLog::add('更新商品属性:' . $this->getName($id));

            return $result !== false;
        }
    }

    /**
     * 更新单个字段
     *
     * @param int $id
     * @param array $data
     * @return bool
     * @throws ApiException
     */
    public function updateProductAttributesField(int $id, array $data): bool
    {
        validate(ProductAttributesValidate::class)->only(array_keys($data))->check($data);
        if (!$id) {
            throw new ApiException('#id错误');
        }
        $result = $this->productAttributesModel::where('attributes_id', $id)->save($data);
        AdminLog::add('更新商品属性:' . $this->getName($id));
        return $result !== false;
    }

    /**
     * 删除商品属性
     *
     * @param int $id
     * @return bool
     */
    public function deleteProductAttributes(int $id): bool
    {
        if (!$id) {
            throw new ApiException('#id错误');
        }
        $get_name = $this->getName($id);
        $result = $this->productAttributesModel::destroy($id);

        if ($result) {
            AdminLog::add('删除商品属性:' . $get_name);
        }

        return $result !== false;
    }

    /**
     * 获取属性
     * @param int $product_id
     * @param string $field
     * @return array|array[]
     */
    public function getAttrList(int $product_id, string $field = ''): array
    {
        // 查询属性值及商品的属性值
        $attr_list = array(
            'normal' => [],
            'spe' => [],
            'extra' => [],
        );
        $type = [
            0 => 'normal',
            1 => 'spe',
            2 => 'extra',
        ];
        if ($product_id) {
            $res = $this->productAttributesModel->where('product_id', $product_id)->field($field)->cursor();
            $attr_arr = array();
            $idx = 0;
            foreach ($res as $row) {
                $type_id = $type[$row['attr_type']];
                $attr_arr[$type_id][$row['attr_name']][] = $row;
                $idx++;
            }
            foreach ($attr_arr as $type => $value) {
                $idx = 0;
                foreach ($value as $k => $row) {
                    $attr_list[$type][$idx]['attr_name'] = $k;
                    $attr_list[$type][$idx]['attr_list'] = $row;
                    $idx++;
                }
            }
        }
        return $attr_list;
    }

    /**
     * 处理属性数据
     * @param int $product_id
     * @param array $attr_list
     * @return bool
     * @throws \Exception
     */
    public function dealProductAttr(int $product_id, array $attr_list = []): bool
    {
        $arr = [];
        foreach ($attr_list as $value) {
            foreach ($value as $row) {
                foreach ($row['attr_list'] as $r) {
                    unset($r['attributes_id']);
                    $arr[] = [
                        'product_id' => $product_id,
                        'attr_type' => isset($r['attr_type']) ? $r['attr_type'] : 0,
                        'attr_name' => isset($r['attr_name']) ? $r['attr_name'] : '',
                        'attr_value' => isset($r['attr_value']) ? $r['attr_value'] : '',
                        'attr_price' => isset($r['attr_price']) ? $r['attr_price'] : '0.00',
                        'attr_color' => isset($r['attr_color']) ? $r['attr_color'] : '',
                        'attr_pic' => isset($r['attr_pic']) ? $r['attr_pic'] : '',
                        'attr_pic_thumb' => isset($r['attr_pic_thumb']) ? $r['attr_pic_thumb'] : '',
                    ];
                }
            }
        }
        $this->productAttributesModel->where('product_id', $product_id)->delete();
        $this->productAttributesModel->saveAll($arr);
        return true;
    }
}
