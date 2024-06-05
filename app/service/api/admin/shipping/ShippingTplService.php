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

namespace app\service\api\admin\shipping;

use app\model\shipping\ShippingTpl;
use app\service\core\BaseService;
use app\validate\shipping\ShippingTplValidate;
use exceptions\ApiException;
use log\AdminLog;

/**
 * 商品规格服务类
 */
class ShippingTplService extends BaseService
{
    protected ShippingTpl $shippingTplModel;
    protected ShippingTplValidate $shippingTplValidate;

    public function __construct(ShippingTpl $shippingTplModel)
    {
        $this->shippingTplModel = $shippingTplModel;
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
        $query = $this->shippingTplModel->query();
        // 处理筛选条件

        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->where('shipping_tpl_name', 'like', '%' . $filter['keyword'] . '%');
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
        $result = $this->shippingTplModel->where('shipping_tpl_id', $id)->find();

        if (!$result) {
            throw new ApiException('商品规格不存在');
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
        return $this->shippingTplModel::where('shipping_tpl_id', $id)->value('shipping_tpl_name');
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
    public function updateShippingTpl(int $id, array $data, bool $isAdd = false)
    {
        validate(ShippingTplValidate::class)->only(array_keys($data))->check($data);
        if ($isAdd) {
            $result = $this->shippingTplModel->save($data);
            AdminLog::add('新增商品规格:' . $data['shipping_tpl_name']);
            return $this->shippingTplModel->getKey();
        } else {
            if (!$id) {
                throw new ApiException('#id错误');
            }
            $result = $this->shippingTplModel->where('shipping_tpl_id', $id)->save($data);
            AdminLog::add('更新商品规格:' . $this->getName($id));

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
    public function updateShippingTplField(int $id, array $data)
    {
        validate(ShippingTplValidate::class)->only(array_keys($data))->check($data);
        if (!$id) {
            throw new ApiException('#id错误');
        }
        $result = $this->shippingTplModel::where('shipping_tpl_id', $id)->save($data);
        AdminLog::add('更新商品规格:' . $this->getName($id));
        return $result !== false;
    }

    /**
     * 删除商品规格
     *
     * @param int $id
     * @return bool
     */
    public function deleteShippingTpl(int $id): bool
    {
        if (!$id) {
            throw new ApiException('#id错误');
        }
        $get_name = $this->getName($id);
        $result = $this->shippingTplModel::destroy($id);

        if ($result) {
            AdminLog::add('删除商品规格:' . $get_name);
        }

        return $result !== false;
    }
    public function getShippingTplList(): array
    {
        $result = $this->shippingTplModel->field('shipping_tpl_id, shipping_tpl_name ,is_default')->where('shop_id',
            request()->shopId)->select();
        return $result->toArray();
    }
}
