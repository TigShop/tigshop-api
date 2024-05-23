<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 会员
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\user;

use app\model\finance\UserBalanceLog;
use app\model\order\Order;
use app\model\product\Product;
use app\model\user\User;
use app\model\user\UserGrowthPointsLog;
use app\model\user\UserPointsLog;
use app\model\user\UserRank;
use app\service\api\admin\authority\AccessTokenService;
use app\service\api\admin\BaseService;
use app\service\api\admin\common\sms\SmsService;
use app\validate\user\UserValidate;
use exceptions\ApiException;
use log\AdminLog;
use utils\Config;
use utils\Time;

/**
 * 会员服务类
 */
class UserService extends BaseService
{
    protected User $userModel;
    protected UserValidate $userValidate;

    public function __construct(User $userModel)
    {
        $this->userModel = $userModel;
    }

    /**
     * 获取筛选结果
     *
     * @param array $filter
     * @return array
     */
    public function getFilterResult(array $filter): array
    {
        $query = $this->filterQuery($filter)->with(["user_rank"])->append(['from_tag_name']);
        if (isset($filter['is_page']) && $filter["is_page"]) {
            $result = $query->field("user_id,username,nickname")->select();
        } else {
            $result = $query->page($filter['page'], $filter['size'])->select();
        }

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
        $query = User::query();
        // 处理筛选条件

        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->where(function ($query) use ($filter) {
                $query->where('username', 'like', '%' . $filter['keyword'] . '%')
                    ->whereOr('mobile', 'like', '%' . $filter['keyword'] . '%')
                    ->whereOr('email', 'like', '%' . $filter['keyword'] . '%');
            });
        }

        // 来源筛选
        if (isset($filter['from_tag']) && $filter["from_tag"] > 0) {
            $query->where('from_tag', $filter['from_tag']);
        }

        // 会员等级
        if (isset($filter['rank_id']) && $filter["rank_id"] > 0) {
            $query->where('rank_id', $filter['rank_id']);
        }

        // 可用金额
        if (isset($filter['balance']) && !empty($filter["balance"])) {
            $query->where('balance', '>', $filter["balance"]);
        }

        // 积分检索
        if (isset($filter['points_gt']) && !empty($filter["points_gt"])) {
            $query->where('points', '>', $filter["points_gt"]);
        }

        if (isset($filter['points_lt']) && !empty($filter["points_lt"])) {
            $query->where('points', '<', $filter["points_lt"]);
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
        $result = User::with(["user_rank",
            "user_address" => function ($query) {
                $query->where("is_default", 1)->field("address_id,user_id,consignee,mobile,telephone,email,region_ids,address,is_default");
            },
        ])->where('user_id', $id)->append(['from_tag_name'])->find();

        if (!$result) {
            throw new ApiException('会员不存在');
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
        return User::where('user_id', $id)->value('username');
    }

    /**
     * 执行会员添加或更新
     *
     * @param int $id
     * @param array $data
     * @param bool $isAdd
     * @return int|bool
     * @throws ApiException
     */
    public function updateUser(int $id, array $data, bool $isAdd = false)
    {
        validate(UserValidate::class)->only(array_keys($data))->check($data);

        $arr = [
            "username" => $data["username"],
            "avatar" => $data["avatar"],
            "mobile" => $data["mobile"],
            "email" => $data["email"],
            "password" => !empty($data["password"]) ? password_hash($data["password"], PASSWORD_DEFAULT) : "",
            "rank_id" => $data["rank_id"],
            "reg_time" => Time::now(),
            'email_validated' => 1,
            "mobile_validated" => 1,
        ];

        if ($data["password"] != $data["pwd_confirm"]) {
            throw new ApiException('两次密码不一致');
        }

        if ($isAdd) {
            $result = User::create($arr);
            AdminLog::add('新增会员:' . $data['username']);
            return $result->getKey();
        } else {
            if (!$id) {
                throw new ApiException('#id错误');
            }
            $result = User::where('user_id', $id)->save($arr);
            AdminLog::add('更新会员:' . $this->getName($id));

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
    public function updateUserField(int $id, array $data)
    {
        validate(UserValidate::class)->only(array_keys($data))->check($data);
        if (!$id) {
            throw new ApiException('#id错误');
        }
        $result = User::where('user_id', $id)->save($data);
        AdminLog::add('更新会员:' . $this->getName($id));
        return $result !== false;
    }

    /**
     * 删除会员
     *
     * @param int $id
     * @return bool
     */
    public function deleteUser(int $id): bool
    {
        if (!$id) {
            throw new ApiException('#id错误');
        }
        $get_name = $this->getName($id);
        $result = User::destroy($id);

        if ($result) {
            AdminLog::add('删除会员:' . $get_name);
        }

        return $result !== false;
    }

    /**
     * 根据账号密码获取会员信息
     *
     * @param string $username
     * @param string $password
     * @return array
     */
    public function getUserByPassword(string $username, string $password): array
    {
        if (!$username || !$password) {
            throw new ApiException('用户名或密码不能为空');
        }
        $item = $this->userModel->where('username', $username)->find();
        if (!$item || !$item['password'] || !password_verify($password, $item['password'])) {
            throw new ApiException('账号名与密码不匹配，请重新输入');
        }
        return $this->getDetail($item['user_id']);
    }

    /**
     * 根据手机短信获取会员信息
     *
     * @param string $mobile
     * @param string $mobile_code
     * @return array
     */
    public function getUserByMobile(string $mobile, string $mobile_code): array
    {
        if (empty($mobile)) {
            throw new ApiException('手机号不能为空');
        }
        if (empty($mobile_code)) {
            throw new ApiException('短信验证码不能为空');
        }
        if (app(SmsService::class)->checkCode($mobile, $mobile_code) == false) {
            throw new ApiException('短信验证码错误或已过期，请重试');
        }
        $item = $this->userModel->where('mobile', $mobile)->find();
        if (!$item) {
            throw new ApiException('不存在此管理员账号，请重试');
        }
        return $this->getDetail($item['user_id']);
    }

    /**
     * 会员登录操作
     *
     * @param int $user_id
     * @param bool $token_login
     * @return array
     */
    public function setLogin(int $user_id, bool $form_login = true): bool
    {
        if (empty($user_id)) {
            throw new ApiException('#uId错误');
        }
        request()->userId = $user_id;
        return true;
    }

    /**
     * 获取token
     *
     * @param integer $user_id
     * @return string
     */
    public function getLoginToken(int $user_id): string
    {
        $token = app(AccessTokenService::class)->setApp('app')->setId($user_id)->createToken();
        return $token;
    }

    // 处理会员默认头像
    public function getUserAvatar(string $avatar = ''): string
    {
        return $avatar ? $avatar : Config::get('default_avatar');
    }

    /**
     * 资金管理
     * @param int $user_id
     * @param array $data
     * @return bool
     * @throws ApiException
     */
    public function fundManagement(int $user_id, array $data)
    {
        if (empty($data["change_desc"])) {
            throw new ApiException('请填写资金变动说明');
        }
        if (array_sum([$data["balance"], $data["frozen_balance"], $data["points"], $data["growth_points"]]) == 0) {
            throw new ApiException('没有帐户变动');
        }

        // 账户资金变动
        $res = $this->changesInFunds($user_id, $data);
        return $res;
    }

    /**
     * 账户资金变动
     * @param int $user_id
     * @param array $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function changesInFunds(int $user_id, array $data)
    {
        $user = User::find($user_id);
        if (empty($user)) {
            throw new ApiException('用户不存在');
        }
        // 用户资金日志
        $user_balance_log = [
            "user_id" => $user_id,
            "change_desc" => $data["change_desc"],
        ];

        // 添加对应的日志
        if (isset($data["balance"]) && !empty($data["balance"])) {
            $user_balance_log["balance"] = $data["balance"];
            $user_balance_log["change_type"] = $data["type_balance"];
            UserBalanceLog::create($user_balance_log);
        }

        if (isset($data["frozen_balance"]) && !empty($data["frozen_balance"])) {
            if (isset($user_balance_log["balance"])) {
                unset($user_balance_log["balance"]);
            }
            $user_balance_log["frozen_balance"] = $data["frozen_balance"];
            $user_balance_log["change_type"] = $data["type_frozen_balance"];
            UserBalanceLog::create($user_balance_log);
            $user_balance_log["balance"] = $data["frozen_balance"];
            $user_balance_log["frozen_balance"] = 0;
            $user_balance_log["change_type"] = $data["type_frozen_balance"] == 1 ? 2 : 1;
            UserBalanceLog::create($user_balance_log);
        }

        if (isset($data["points"]) && !empty($data["points"])) {
            $user_balance_log["points"] = $data["points"];
            $user_balance_log["change_type"] = $data["type_points"];
            UserPointsLog::create($user_balance_log);
        }
        if (isset($data["growth_points"]) && !empty($data["growth_points"])) {
            $user_balance_log["points"] = $data["growth_points"];
            $user_balance_log["change_type"] = $data["type_growth_points"];
            UserGrowthPointsLog::create($user_balance_log);
        }

        // 更新用户资金信息
        if (isset($data['balance']) && $data['balance'] > 0) {
            if ($data["type_balance"] == 1) {
                $user = $user->inc('balance', $data["balance"]);
            } else {
                $user = $user->dec('balance', $data["balance"]);
            }
        }
        if (isset($data['frozen_balance']) && $data['frozen_balance'] > 0) {
            if ($data["type_frozen_balance"] == 1) {
                $user = $user->inc('frozen_balance', $data["frozen_balance"]);
                $user = $user->dec('balance', $data["frozen_balance"]);
            } else {
                $user = $user->dec('frozen_balance', $data["frozen_balance"]);
                $user = $user->inc('balance', $data["frozen_balance"]);
            }
        }
        if (isset($data['points']) && $data['points'] > 0) {
            if ($data["type_points"] == 1) {
                $user = $user->inc('points', $data["points"]);
            } else {
                $user = $user->dec('points', $data["points"]);
            }
        }
        if (isset($data['growth_points']) && $data['growth_points'] > 0) {
            if ($data["type_growth_points"] == 1) {
                $user = $user->inc('growth_points', $data["growth_points"]);
            } else {
                $user = $user->dec('growth_points', $data["growth_points"]);
            }
        }

        $user->update();

        //根据用户积分判断是否升级用户等级
        $res = $this->updateUserRank($user_id);

        return $res !== false;
    }

    /**
     * 根据用户积分判断是否升级用户等级
     * @param int $user_id
     * @return true
     * @throws ApiException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function updateUserRank(int $user_id)
    {
        $user = User::find($user_id);
        if (empty($user)) {
            throw new ApiException('用户不存在');
        }

        $user_rank_original = UserRank::findOrEmpty($user->rank_id);
        // 排除固定等级用户
        if ($user_rank_original->rank_type == 2) {
            return true;
        }
        $user_rank_current = UserRank::where('min_growth_points', '<=', $user->points)
            ->where("rank_type", 1)
            ->order("min_growth_points", "desc")
            ->limit(1)
            ->find();

        if (!empty($user_rank_current) && $user_rank_current->rank_id != $user->rank_id) {
            $user->rank_id = $user_rank_current->rank_id;
            return $user->save();
        }
        return true;
    }

    /**
     * 添加商品记录到用户
     * @param $product_id
     * @return bool
     */
    public function addProductHistory(int $user_id, int $product_id): bool
    {
        Product::where('product_id', $product_id)->inc('click_count')->save();
        $user = User::find($user_id);
        if (!$user) {
            return true;
        }
        $history_product_ids = [];
        if ($user['history_product_ids']) {
            $history_product_ids = json_decode($user['history_product_ids'], true);
        }
        array_unshift($history_product_ids, $product_id);
        $history_product_ids = array_slice(array_unique($history_product_ids), 0, 20);
        $user->history_product_ids = json_encode($history_product_ids);
        return $user->save();
    }

    /**
     * 判断是否是新人
     * @param int $user_id
     * @return bool
     */
    public function isNew(int $user_id): bool
    {
        $count = Order::where('user_id', $user_id)->where('pay_status', '>', 0)->count();
        return !$count;
    }

    /**
     * 获取用户会员等级
     * @param int $user_id
     * @return int
     */
    public function getUserRankId(int $user_id): int
    {
        return User::where('user_id', $user_id)->value('rank_id');
    }

    /**
     * 扣除积分
     * @param int $point
     * @param int $user_id
     * @return bool
     */
    public function decPoints(int $point, int $user_id, string $change_desc = '减积分'): bool
    {
        $log = [
            "user_id" => $user_id,
            "points" => $point,
            "change_type" => 2,
            "change_desc" => $change_desc,
        ];
        UserPointsLog::create($log);
        return User::where('user_id', $user_id)->dec('points', $point)->save();
    }

    /**
     * 增加积分
     * @param int $point
     * @param int $user_id
     * @return bool
     */
    public function incPoints(int $point, int $user_id, string $change_desc = '加积分'): bool
    {
        $log = [
            "user_id" => $user_id,
            "points" => $point,
            "change_type" => 1,
            "change_desc" => $change_desc,
        ];
        UserPointsLog::create($log);
        return User::where('user_id', $user_id)->inc('points', $point)->save();
    }

    /**
     * 扣除余额
     * @param float $balance
     * @param int $user_id
     * @param string $change_desc
     * @return bool
     */
    public function decBalance(float $balance, int $user_id, string $change_desc = '减余额'): bool
    {
        $result = User::where('user_id', $user_id)->dec('balance', $balance)->save();
        $user_balance_log = [
            "user_id" => $user_id,
            "change_desc" => $change_desc,
            "balance" => $balance,
            "change_type" => 2,
        ];
        UserBalanceLog::create($user_balance_log);
        return $result;
    }

    /**
     * 加余额
     * @param float $balance
     * @param int $user_id
     * @param string $change_desc
     * @return bool
     */
    public function incBalance(float $balance, int $user_id, string $change_desc = '加余额'): bool
    {
        $result = User::where('user_id', $user_id)->inc('balance', $balance)->save();
        $user_balance_log = [
            "user_id" => $user_id,
            "change_desc" => $change_desc,
            "balance" => $balance,
            "change_type" => 1,
        ];
        UserBalanceLog::create($user_balance_log);
        return $result;
    }

    /**
     * 扣除冻结余额
     * @param float $frozen_balance
     * @param int $user_id
     * @param string $change_desc
     * @return bool
     */
    public function decFrozenBalance(float $frozen_balance, int $user_id, string $change_desc = '减冻结余额'): bool
    {
        $result = User::where('user_id', $user_id)->dec('frozen_balance', $frozen_balance)->save();
        $user_balance_log = [
            "user_id" => $user_id,
            "change_desc" => $change_desc,
            "frozen_balance" => $user_id,
            "change_type" => 2,
        ];
        UserBalanceLog::create($user_balance_log);
        return $result;
    }

    /**
     * 增加冻结余额
     * @param float $frozen_balance
     * @param int $user_id
     * @param string $change_desc
     * @return bool
     */
    public function incFrozenBalance(float $frozen_balance, int $user_id, string $change_desc = '增加冻结余额'): bool
    {
        $result = User::where('user_id', $user_id)->inc('frozen_balance', $frozen_balance)->save();
        $user_balance_log = [
            "user_id" => $user_id,
            "change_desc" => $change_desc,
            "frozen_balance" => $frozen_balance,
            "change_type" => 1,
        ];
        UserBalanceLog::create($user_balance_log);
        return $result;
    }

}
