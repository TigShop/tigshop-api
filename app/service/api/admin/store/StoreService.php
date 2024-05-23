<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 店铺
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\store;

use app\model\store\Store;
use app\service\api\admin\BaseService;
use app\validate\store\StoreValidate;
use exceptions\ApiException;
use log\AdminLog;

/**
 * 店铺服务类
 */
class StoreService extends BaseService
{
    protected Store $storeModel;
    protected StoreValidate $storeValidate;

    public function __construct(Store $storeModel)
    {
        $this->storeModel = $storeModel;
    }

    /**
     * 获取筛选结果
     *
     * @param array $filter
     * @return array
     */
    public function getFilterResult(array $filter): array
    {
        $query = $this->filterQuery($filter)->with(['userName']);
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
        $query = $this->storeModel->query();
        // 处理筛选条件

        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->where('store_title', 'like', '%' . $filter['keyword'] . '%');
        }

        if (isset($filter['store_id']) && $filter['store_id'] > 0) {
            $query->where('store_id', $filter['store_id']);
        }

        if (isset($filter['is_self']) && $filter['is_self'] > -1) {
            $query->where('is_self', $filter['is_self']);
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
        $result = $this->storeModel->where('store_id', $id)->find();

        if (!$result) {
            throw new ApiException('店铺不存在');
        }

        return $result->toArray();
    }

    /**
     * 获取所有店铺,用于select选择
     *
     * @return array
     * @throws ApiException
     */
    public function getAllStore(): array
    {
        $result = $this->storeModel->field('store_id,store_title')->select();
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
        return $this->storeModel->where('store_id', $id)->value('store_title');
    }

    /**
     * 执行店铺添加或更新
     *
     * @param int $id
     * @param array $data
     * @param bool $isAdd
     * @return int|bool
     * @throws ApiException
     */
    public function updateStore(int $id, array $data, bool $isAdd = false)
    {
        validate(StoreValidate::class)->only(array_keys($data))->check($data);
        if ($isAdd) {
            $result = $this->storeModel->create($data);
            AdminLog::add('新增店铺:' . $data['store_title']);
            return $this->storeModel->getKey();
        } else {
            if (!$id) {
                throw new ApiException('#id错误');
            }
            $result = $this->storeModel->where('store_id', $id)->save($data);
            AdminLog::add('更新店铺:' . $this->getName($id));

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
    public function updateStoreField(int $id, array $data)
    {
        validate(StoreValidate::class)->only(array_keys($data))->check($data);
        if (!$id) {
            throw new ApiException('#id错误');
        }
        $result = $this->storeModel::where('store_id', $id)->save($data);
        AdminLog::add('更新店铺:' . $this->getName($id));
        return $result !== false;
    }

    /**
     * 删除店铺
     *
     * @param int $id
     * @return bool
     */
    public function deleteStore(int $id): bool
    {
        if (!$id) {
            throw new ApiException('#id错误');
        }
        $get_name = $this->getName($id);
        $result = $this->storeModel->destroy($id);

        if ($result) {
            AdminLog::add('删除店铺:' . $get_name);
        }

        return $result !== false;
    }
}
