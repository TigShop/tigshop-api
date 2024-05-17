<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 商品属性模板
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\product;

use app\common\exceptions\ApiException;
use app\common\log\AdminLog;
use app\model\product\ProductAttributesTpl;
use app\service\BaseService;
use app\validate\product\ProductAttributesTplValidate;

/**
 * 商品属性模板服务类
 */
class ProductAttributesTplService extends BaseService
{
    protected ProductAttributesTpl $productAttributesTplModel;
    protected ProductAttributesTplValidate $productAttributesTplValidate;

    public function __construct(ProductAttributesTpl $productAttributesTplModel)
    {
        $this->productAttributesTplModel = $productAttributesTplModel;
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

    public function getAttrTplList()
    {
        $result = $this->productAttributesTplModel->field('tpl_id,tpl_name')->select();
        return $result->toArray();
    }

    /**
     * 筛选查询
     *
     * @param array $filter
     * @return object
     */
    protected function filterQuery(array $filter): object
    {
        $query = $this->productAttributesTplModel->query();
        // 处理筛选条件

        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->where('tpl_name', 'like', '%' . $filter['keyword'] . '%');
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
        $result = $this->productAttributesTplModel->where('tpl_id', $id)->find();

        if (!$result) {
            throw new ApiException('商品属性模板不存在');
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
        return $this->productAttributesTplModel::where('tpl_id', $id)->value('tpl_name');
    }

    /**
     * 执行商品属性模板添加或更新
     *
     * @param int $id
     * @param array $data
     * @param bool $isAdd
     * @return int|bool
     * @throws ApiException
     */
    public function updateProductAttributesTpl(int $id, array $data, bool $isAdd = false): int | bool
    {
        validate(ProductAttributesTplValidate::class)->only(array_keys($data))->check($data);
        if ($isAdd) {
            $result = $this->productAttributesTplModel->save($data);
            AdminLog::add('新增商品属性模板:' . $data['tpl_name']);
            return $this->productAttributesTplModel->getKey();
        } else {
            if (!$id) {
                throw new ApiException('#id错误');
            }
            $result = $this->productAttributesTplModel->where('tpl_id', $id)->save($data);
            AdminLog::add('更新商品属性模板:' . $this->getName($id));

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
    public function updateProductAttributesTplField(int $id, array $data): bool
    {
        validate(ProductAttributesTplValidate::class)->only(array_keys($data))->check($data);
        if (!$id) {
            throw new ApiException('#id错误');
        }
        $result = $this->productAttributesTplModel::where('tpl_id', $id)->save($data);
        AdminLog::add('更新商品属性模板:' . $this->getName($id));
        return $result !== false;
    }

    /**
     * 删除商品属性模板
     *
     * @param int $id
     * @return bool
     */
    public function deleteProductAttributesTpl(int $id): bool
    {
        if (!$id) {
            throw new ApiException('#id错误');
        }
        $get_name = $this->getName($id);
        $result = $this->productAttributesTplModel::destroy($id);

        if ($result) {
            AdminLog::add('删除商品属性模板:' . $get_name);
        }

        return $result !== false;
    }
}
