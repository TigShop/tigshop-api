<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 增票资质申请
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\finance;

use app\model\finance\UserInvoice;
use app\service\core\BaseService;
use app\validate\finance\UserInvoiceValidate;
use exceptions\ApiException;

/**
 * 增票资质申请服务类
 */
class UserInvoiceService extends BaseService
{
    protected UserInvoiceValidate $userInvoiceValidate;

    public function __construct()
    {
    }

    /**
     * 获取筛选结果
     *
     * @param array $filter
     * @return array
     */
    public function getFilterResult(array $filter): array
    {
        $query = $this->filterQuery($filter)->with(["user"])->append(["status_name", "title_type_name"]);
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
    public function filterQuery(array $filter): object
    {
        $query = UserInvoice::query();
        // 处理筛选条件

        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->keyword($filter['keyword']);
        }

        // 审核状态
        if (isset($filter['status']) && $filter['status'] != -1 && $filter['status'] != 0) {
            $query->where('status', $filter['status']);
        }

        if (isset($filter['sort_field'], $filter['sort_order']) && !empty($filter['sort_field']) && !empty($filter['sort_order'])) {
            $query->order($filter['sort_field'], $filter['sort_order']);
        }

        // 过滤非专用发票
        $query->where('status', "<>", 0);
        return $query;
    }

    /**
     * 获取详情
     *
     * @param int $id
     * @return UserInvoice
     * @throws ApiException
     */
    public function getDetail(int $id): UserInvoice
    {
        $result = UserInvoice::with(["user"])->where('invoice_id', $id)->append(["status_name", "title_type_name"])->find();

        if (!$result) {
            throw new ApiException(/** LANG */'增票资质申请不存在');
        }

        return $result;
    }

    /**
     * 获取名称
     *
     * @param int $id
     * @return string|null
     */
    public function getName(int $id): ?string
    {
        return UserInvoice::where('invoice_id', $id)->value('company_name');
    }

    /**
     * 更新增票资质申请
     * @param int $id
     * @param array $data
     * @return bool
     * @throws ApiException
     */
    public function updateQualificationApply(int $id,array $data): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = UserInvoice::where('invoice_id', $id)->save($data);
        return $result !== false;
    }

    /**
     * 获取通用数据
     * @param array $data
     * @return array
     * @throws ApiException
     */
    public function getCommonData(array $data): array
    {
        validate(UserInvoiceValidate::class)->only(array_keys($data))->check($data);
        if (isset($data["is_confirm"]) && $data["is_confirm"] != 1) {
            throw new ApiException(/** LANG */'请勾选已阅读并同意《增票资质确认书》');
        }
        if (isset($data["invoice_type"])) {
            if ($data["invoice_type"]) {
                // 增票资质专票
                $data["status"] = 2;
            } else {
                // 增票资质普票
                $data["status"] = 0;
            }
        }
        unset($data["invoice_type"]);
        unset($data["is_confirm"]);
        return $data;
    }


    /**
     * 创建增票资质申请
     * @param int $user_id
     * @param array $data
     * @return int
     * @throws ApiException
     * @throws \think\db\exception\DbException
     */
    public function createUserInvoice(int $user_id, array $data): int
    {
        $data = $this->getCommonData($data);
        $data["user_id"] = $user_id;
        if (UserInvoice::where("user_id", $user_id)->count()) {
            throw new ApiException(/** LANG */'您已申请过增值税专用发票开票资质，请勿重复申请');
        }
        $result = UserInvoice::create($data);
        return $result->invoice_id;
    }

    /**
     * 执行增票资质申请更新
     * @param int $id
     * @param array $data
     * @return bool
     * @throws ApiException
     * @throws \think\db\exception\DbException
     */
    public function updateUserInvoice(int $id, int $user_id, array $data): bool
    {
        $data = $this->getCommonData($data);
        $data["user_id"] = $user_id;
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = UserInvoice::where('invoice_id', $id)->save($data);
        return $result !== false;
    }

    /**
     * 删除增票资质申请
     *
     * @param int $id
     * @return bool
     */
    public function deleteUserInvoice(int $id): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = UserInvoice::destroy($id);
        return $result !== false;
    }

    /**
     * Pc 端获取用户增票资质详情
     *
     * @return array
     * @throws ApiException
     */
    public function getUserInvoiceDetail(): array
    {
        $result = UserInvoice::where('user_id', request()->userId)->findOrEmpty()->toArray();
        if (empty($result)) {
            $result['status'] = 0;
        }

        return $result;
    }

    /**
     * 判断当前用户的增票资质是否审核通过
     * @param int $user_id
     * @return mixed
     */
    public function getInvoiceStatus(int $user_id): mixed
    {
        $user_invoice = UserInvoice::where('user_id', $user_id)->findOrEmpty();
        if ($user_invoice->status == UserInvoice::STATUS_APPROVED) {
            return $user_invoice->toArray();
        }
        return false;
    }
}
