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

use app\model\merchant\Apply;
use app\service\core\BaseService;
use exceptions\ApiException;

/**
 * 商户申请服务类
 */
class ApplyService extends BaseService
{
    protected Apply $merchantApplyModel;

    public function __construct(Apply $merchantApplyModel)
    {
        $this->merchantApplyModel = $merchantApplyModel;
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
        $query = $this->merchantApplyModel->query()->withJoin(['user']);
        // 处理筛选条件

        if (isset($filter['username']) && !empty($filter['username'])) {
            $query->where('username', 'like', '%' . $filter['username'] . '%');
        }

        if (isset($filter['status']) && $filter['status'] > 0) {
            $query->where('status', $filter['status']);
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
     * @return Apply
     * @throws ApiException
     */
    public function getDetail(int $id): Apply
    {
        $result = $this->merchantApplyModel->find($id);

        if (!$result) {
            throw new ApiException('商户入驻申请信息不存在');
        }

        return $result;
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
            $result = $this->merchantApplyModel->create($data);
            return $this->merchantApplyModel->getKey();
        } else {
            if (!$id) {
                throw new ApiException('#id错误');
            }
            $result = $this->merchantApplyModel->where('merchant_apply_id', $id)->save($data);
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
    public function audit(int $id, int $status, string $auditRemark): bool|int
    {
        $result = $this->merchantApplyModel->where('merchant_apply_id', $id)->save([
            'status' => $status,
            'audit_time' => time(),
            'audit_remark' => $auditRemark
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
        $result = $this->merchantApplyModel::where('merchant_apply_id', $id)->save($data);
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
        $result = $this->merchantApplyModel->destroy($id);
        return $result !== false;
    }
}
