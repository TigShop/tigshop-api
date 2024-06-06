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

namespace app\service\api\admin\merchant;

use app\model\merchant\Shop;
use app\service\core\BaseService;
use exceptions\ApiException;
use log\AdminLog;

/**
 * 店铺服务类
 */
class ShopService extends BaseService
{

    public function __construct(Shop $shopModel)
    {
        $this->model = $shopModel;
    }


    /**
     * 筛选查询
     *
     * @param array $filter
     * @return object
     */
    protected function filterQuery(array $filter): object
    {
        $query = $this->model->query();
        // 处理筛选条件

        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->where('shop_title', 'like', '%' . $filter['keyword'] . '%');
        }

        if (isset($filter['shop_id']) && $filter['shop_id'] > 0) {
            $query->where('shop_id', $filter['shop_id']);
        }

        if (isset($filter['merchant_id']) && $filter['merchant_id'] > -1) {
            $query->where('merchant_id', $filter['merchant_id']);
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
        $result = $this->model->where('shop_id', $id)->find();

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
        $result = $this->model->field('shop_id,store_title')->select();
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
        return $this->model->where('shop_id', $id)->value('store_title');
    }

    /**
     * 创建店铺
     * @param array $data
     * @return Shop|\think\Model
     */
    public function create(array $data): Shop|\think\Model
    {
        $result = $this->model->create($data);
        return $result;
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
    public function updateShop(int $id, array $data, bool $isAdd = false): bool|int
    {

        if ($isAdd) {
            $result = $this->model->create($data);
            return $this->model->getKey();
        } else {
            if (!$id) {
                throw new ApiException('#id错误');
            }
            $result = $this->model->where('shop_id', $id)->save($data);
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
    public function updateShopField(int $id, array $data): bool|int
    {
        $result = $this->model::where('shop_id', $id)->save($data);
        return $result !== false;
    }

    /**
     * 删除店铺
     *
     * @param int $id
     * @return bool
     */
    public function deleteShop(int $id): bool
    {
        if (!$id) {
            throw new ApiException('#id错误');
        }
        $get_name = $this->getName($id);
        $result = $this->model->destroy($id);

        if ($result) {
            AdminLog::add('删除店铺:' . $get_name);
        }

        return $result !== false;
    }
}
