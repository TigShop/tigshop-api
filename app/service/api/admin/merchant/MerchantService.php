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

use app\model\merchant\Merchant;
use app\model\merchant\MerchantUser;
use app\service\core\BaseService;
use app\service\core\merchant\MerchantCoreService;
use exceptions\ApiException;

/**
 * 商户服务类
 */
class MerchantService extends BaseService
{
    protected Merchant $merchantModel;
    protected MerchantCoreService $merchantCoreService;

    public function __construct(Merchant $merchantModel, MerchantCoreService $merchantCoreService)
    {
        $this->merchantModel = $merchantModel;
        $this->merchantCoreService = $merchantCoreService;
    }

    /**
     * 筛选查询
     *
     * @param array $filter
     * @return object
     */
    protected function filterQuery(array $filter): object
    {
        $query = $this->merchantModel->query();
        // 处理筛选条件

        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->where('store_title', 'like', '%' . $filter['keyword'] . '%');
        }

        if (isset($filter['shop_id']) && $filter['shop_id'] > 0) {
            $query->where('shop_id', $filter['shop_id']);
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
    public function getDetail(int $id): Merchant
    {
        $result = $this->merchantCoreService->getDetail($id);

        if (!$result) {
            throw new ApiException('店铺不存在');
        }
        $result->user;
        return $result;
    }

    /**
     * 创建商户
     * @param array $data
     * @return \think\Model|Merchant
     */
    public function create(array $data): Merchant|\think\Model
    {
        return $this->merchantModel->create($data);
    }

    /**
     * 执行添加或更新
     *
     * @param int $id
     * @param array $data
     * @param bool $isAdd
     * @return int|bool
     * @throws ApiException
     */
    public function update(int $id, array $data, bool $isAdd = false)
    {
        if ($isAdd) {
            $result = $this->merchantModel->create($data);
            return $this->merchantModel->getKey();
        } else {
            if (!$id) {
                throw new ApiException('#id错误');
            }
            $result = $this->merchantModel->where('merchant_apply_id', $id)->save($data);
            return $result !== false;
        }
    }

    /**
     * 执行审核
     *
     * @param int $id
     * @param int $status
     * @return int|bool
     * @throws ApiException
     */
    public function audit(int $id, int $status)
    {
        $result = $this->merchantModel->where('merchant_apply_id', $id)->save([
            'status' => $status,
            'audit_time' => time(),
        ]);
        return $result !== false;
    }

    /**
     * 更新单个字段
     *
     * @param int $id
     * @param array $data
     * @return int|bool
     * @throws ApiException
     */
    public function updateField(int $id, array $data)
    {
        if (!$id) {
            throw new ApiException('#id错误');
        }
        $result = $this->merchantModel::where('merchant_apply_id', $id)->save($data);
        return $result !== false;
    }

    /**
     * 删除
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        if (!$id) {
            throw new ApiException('#id错误');
        }
        $result = $this->merchantModel->destroy($id);
        return $result !== false;
    }

    public function createUser(array $data)
    {
        return MerchantUser::create($data);
    }
}
