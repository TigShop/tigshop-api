<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 提现申请
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\finance;

use app\common\exceptions\ApiException;
use app\common\log\AdminLog;
use app\common\utils\Time;
use app\model\finance\UserWithdrawAccount;
use app\model\finance\UserWithdrawApply;
use app\model\user\User;
use app\service\BaseService;
use app\service\user\UserService;
use think\facade\Db;

/**
 * 提现申请服务类
 */
class UserWithdrawApplyService extends BaseService
{
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
        $query = $this->filterQuery($filter)->with(["user"])->append(["status_type"]);
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
        $query = UserWithdrawApply::query();
        // 处理筛选条件

        if (isset($filter["keyword"]) && !empty($filter['keyword'])) {
            $query->username($filter["keyword"]);
        }

        // 状态检索
        if (isset($filter["status"]) && $filter["status"] > -1) {
            $query->where('status', $filter["status"]);
        }

        if (isset($filter["user_id"]) && $filter["user_id"] > 0) {
            $query->where('user_id', $filter["user_id"]);
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
        $result = UserWithdrawApply::where('id', $id)->append(["status_type"])->find();

        if (!$result) {
            throw new ApiException(/** LANG */'提现申请不存在');
        }

        return $result->toArray();
    }

    /**
     * 提现申请余额操作
     * @param array $data
     * @return void
     */
    public function balanceOperation(array $data): void
    {
        // 处理状态已完成
        if ($data["status"] == 1) {
            //减去用户冻结的余额
            app(UserService::class)->decFrozenBalance($data["amount"], $data["user_id"], '提现审核通过扣减冻结余额');
        }
        if ($data["status"] == 2) {
            //拒绝后返回余额
            app(UserService::class)->incBalance($data["amount"], $data["user_id"], '提现审核拒绝返回余额');
        }
    }



    /**
     * 添加提现申请
     * @param array $data
     * @return bool
     * @throws ApiException
     */
    public function createUserWithdrawApply(array $data): bool
    {
        if ($data["status"] == 1) {
            $data["finished_time"] = Time::now();
        }
        // 判断提现金额
        $balance = User::find($data['user_id'])->balance;
        if ($data["amount"] > $balance) {
            throw new ApiException(/** LANG */'提现金额大于账户的可用余额');
        }

        $result = UserWithdrawApply::create($data);
        if (in_array($data["status"], [0, 1])) {
            // 保存账号信息
            $data["account_data"]["user_id"] = $data["user_id"];
            UserWithdrawAccount::create($data["account_data"]);
        }

        if ($result !== false) {
            $this->balanceOperation($data);
            return true;
        }
        return false;
    }

    /**
     * 执行提现申请更新
     *
     * @param int $id
     * @param array $data
     * @param bool $isAdd
     * @return int|bool
     * @throws ApiException
     */
    public function updateUserWithdrawApply(int $id, array $data)
    {
        if ($data["status"] == 1) {
            $data["finished_time"] = Time::now();
        }
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        if (UserWithdrawApply::find($id)->status > 0) {
            throw new ApiException(/** LANG */'该笔提现申请已完成，不能修改');
        }
        $result = UserWithdrawApply::where('id', $id)->save($data);

        if ($result !== false) {
            $this->balanceOperation($data);
            return true;
        }
        return false;
    }

    /**
     * 删除提现申请
     *
     * @param int $id
     * @return bool
     */
    public function deleteUserWithdrawApply(int $id): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = UserWithdrawApply::destroy($id);
        return $result !== false;
    }

    /**
     * PC 提现账号列表
     * @param array $filter
     * @param int $user_id
     * @return array|\think\Collection|\think\db\BaseQuery[]
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAccountList(array $filter, int $user_id)
    {
        $query = UserWithdrawAccount::query();
        // 处理筛选条件
        if (isset($filter["account_type"]) && !empty($filter["account_type"])) {
            $query->where('account_type', $filter["account_type"]);
        }
        if (isset($filter["account_id"]) && !empty($filter['account_id'])) {
            $query->where('account_id', $filter["account_id"]);
        }
        if ($user_id > 0) {
            $query->where('user_id', $user_id);
        }
        return $query->append(["account_type_name"])->select();
    }

    /**
     * PC 添加提现账号
     * @param array $data
     * @param int $user_id
     * @return bool
     */
    public function addWithdrawAccount(array $data, int $user_id)
    {
        if (UserWithdrawAccount::where('user_id', $user_id)->count() > 15) {
            throw new ApiException(/** LANG */'最多添加15个卡');
        }
        $data["user_id"] = $user_id;
        $result = UserWithdrawAccount::create($data);
        return $result !== false;
    }

    /**
     * 删除提现账号
     * @param array $data
     * @param int $user_id
     * @return bool
     */
    public function delWithdrawAccount(int $account_id, int $user_id)
    {
        $result = UserWithdrawAccount::where('user_id', $user_id)->where('account_id', $account_id)->delete();
        return $result !== false;
    }

    /**
     * 编辑提现账号
     * @param array $data
     * @param int $user_id
     * @return bool
     */
    public function editWithdrawAccount(int $account_id, int $user_id, array $data)
    {
        $result = UserWithdrawAccount::where('user_id', $user_id)->where('account_id', $account_id)->update($data);
        return $result !== false;
    }

    /**
     * 提现账号详情
     * @param array $data
     * @param int $user_id
     * @return bool
     */
    public function withdrawAccountDetail(int $account_id, int $user_id)
    {
        $result = UserWithdrawAccount::where('user_id', $user_id)->where('account_id', $account_id)->find();
        return $result;
    }

    /**
     * 提现申请
     * @param array $data
     * @param int $user_id
     * @return true
     * @throws ApiException
     */
    public function updateUserWithdrawApplyPc(array $data, int $user_id): bool
    {
        if (empty($data["account_data"])) {
            throw new ApiException(/** LANG */'请填写提现账号信息');
        }
        $balance = User::findOrEmpty($user_id)->balance;
        if ($data["amount"] > $balance) {
            throw new ApiException(/** LANG */'提现金额大于账户的可用余额');
        }
        $data["user_id"] = $user_id;
        try {
            Db::startTrans();
            $result = UserWithdrawApply::create($data);
            app(UserService::class)->incFrozenBalance($data['amount'], $user_id, '提现冻结余额');
            app(UserService::class)->decBalance($data['amount'], $user_id, '提现扣除余额');
            Db::commit();

            return true;
        } catch (\Exception $exception) {
            Db::rollback();
            throw new ApiException($exception->getMessage());
        }
    }
}
