<?php

namespace app\service\api\admin\authority;

use app\model\authority\AdminRole;
use app\model\authority\AdminUser;
use app\model\merchant\MerchantUser;
use app\model\merchant\Shop;
use app\service\api\admin\common\sms\SmsService;
use app\service\core\BaseService;
use exceptions\ApiException;
use log\AdminLog;

/**
 * 管理员服务类
 */
class AdminUserService extends BaseService
{
    protected AdminUser $adminUserModel;

    public function __construct(AdminUser $adminUserModel)
    {
        $this->adminUserModel = $adminUserModel;
    }

    /**
     * 获取筛选结果
     *
     * @param array $filter
     * @return array
     */
    public function getFilterResult(array $filter): array
    {
        $query = $this->filterQuery($filter)->with(["role"]);
        $result = $query->field('c.*, COUNT(s.admin_id) AS has_children')
            ->leftJoin('admin_user s', 'c.admin_id = s.parent_id')
            ->group('c.admin_id')->page($filter['page'], $filter['size'])->select();
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
        $query = $this->adminUserModel->query()->alias('c');
        // 处理筛选条件

        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->where('c.username', 'like', '%' . $filter['keyword'] . '%');
        }
        if (!empty($filter['admin_type'])) {
            $query->where('c.admin_type', $filter['admin_type']);
        }
        // 供应商查询
        if (isset($filter['suppliers_id']) && $filter['suppliers_id'] > 0) {
            $query->where('c.suppliers_id', $filter['suppliers_id']);
        }

        if (isset($filter['parent_id']) && !empty($filter['parent_id'])) {
            $query->where('c.parent_id', $filter['parent_id']);
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
     * @return AdminUser
     * @throws ApiException
     */
    public function getDetail(int $id): AdminUser
    {
        $result = $this->adminUserModel->withoutField('password')->where('admin_id', $id)->find();
        if (!$result) {
            throw new ApiException(/** LANG */'管理员不存在');
        }

        if ($result['role_id '] > 0) {
            // 获取权限组权限
            $result['auth_list'] = AdminRole::find($result['role_id'])->authority_list;
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
        return $this->adminUserModel::where('admin_id', $id)->value('username');
    }

    /**
     * 获取添加/更新的通用数据
     * @param array $data
     * @return array
     * @throws ApiException
     */
    public function getCommunalData(array $data):array
    {
        $arr = [
            "username" => $data["username"],
            "mobile" => $data["mobile"],
            "email" => $data["email"],
            "role_id" => $data["role_id"],
            "merchant_id" => $data["merchant_id"] ?? 0,
            'avatar' => $data['avatar'],
            'admin_type' => !empty($data['admin_type']) ? $data['admin_type'] : 'admin',
        ];
        if (empty($arr['avatar'])) {
            $rand = rand(1, 34);
            $arr['avatar'] = '../assets/avatar/' . $rand . '.jpeg';
        }

        if (!empty($data["password"])) {
            $arr["password"] = password_hash($data["password"], PASSWORD_DEFAULT);
        }

        if ($data["password"] != $data["pwd_confirm"]) {
            throw new ApiException(/** LANG */'密码不一致');
        }

        if ($data["role_id"] > 0) {
            $arr["auth_list"] = AdminRole::find($data["role_id"])->authority_list;
        } else {
            if ($data["checkall"]) {
                // 全选
                $arr["auth_list"] = ["all"];
            } else {
                $arr["auth_list"] = $data["auth_list"];
            }
        }
        return $arr;
    }

    /**
     * 执行添加操作
     * @param array $data
     * @return array
     * @throws ApiException
     */
    public function createAdminUser(array $data): array|int
    {
        $arr = $this->getCommunalData($data);
        $result = $this->adminUserModel->create($arr);
        AdminLog::add('新增管理员:' . $data['username']);
        $admin_id = $result->admin_id;
        $this->updateMerchantUser($data, $admin_id);
        return $admin_id;
    }


    /**
     * 执行管理员更新
     *
     * @param int $id
     * @param array $data
     * @return bool
     * @throws ApiException
     */
    public function updateAdminUser(array $data, int $id):bool
    {
        $arr = $this->getCommunalData($data);
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = $this->adminUserModel->where('admin_id', $id)->save($arr);
        $this->updateMerchantUser($data, $id);
        return $result !== false;
    }

    /**
     * 更新商户用户
     * @param array $data
     * @param int $id
     * @return bool
     * @throws \think\db\exception\DbException
     */
    public function updateMerchantUser(array $data, int $id): bool
    {
        if (empty($data['merchant_id']) || empty($data['user_id'])) {
            return true;
        }
        if (MerchantUser::where('merchant_id', $data['merchant_id'])->where('admin_user_id', $id)->count() == 0) {
            MerchantUser::where('merchant_id', $data['merchant_id'])->where('admin_user_id', $id)->update([
                'user_id' => $data['user_id'],
            ]);
        } else {
            MerchantUser::create([
                'user_id' => $data['user_id'],
                'merchant_id' => $data['merchant_id'],
                'admin_user_id' => $id,
            ]);
        }
        return true;
    }

    /**
     * 删除管理员
     *
     * @param int $id
     * @return bool
     */
    public function deleteAdminUser(int $id): bool
    {
        $get_name = $this->getName($id);
        $result = $this->adminUserModel::destroy($id);

        if ($result) {
            AdminLog::add('删除管理员:' . $get_name);
        }
        return $result !== false;
    }
    /**
     * 根据账号密码获取会员信息
     *
     * @param string $username
     * @param string $password
     * @return AdminUser
     */
    public function getAdminUserByPassword(string $username, string $password): AdminUser
    {
        if (!$username || !$password) {
            throw new ApiException(/** LANG */'用户名或密码不能为空');
        }
        $item = $this->adminUserModel->where('username', $username)->find();
        if (!$item || !$item['password'] || !password_verify($password, $item['password'])) {
            throw new ApiException(/** LANG */'管理员账号或密码错误，请重试');
        }
        return $this->getDetail($item['admin_id']);
    }
    /**
     * 根据手机短信获取会员信息
     *
     * @param string $mobile
     * @param string $mobile_code
     * @return AdminUser
     */
    public function getAdminUserByMobile(string $mobile, string $mobile_code): AdminUser
    {
        if (empty($mobile)) {
            throw new ApiException(/** LANG */'手机号不能为空');
        }
        if (empty($mobile_code)) {
            throw new ApiException(/** LANG */'短信验证码不能为空');
        }
        if (app(SmsService::class)->checkCode($mobile, $mobile_code) == false) {
            throw new ApiException(/** LANG */'短信验证码错误或已过期，请重试');
        }
        $item = $this->adminUserModel->where('mobile', $mobile)->find();
        if (!$item) {
            throw new ApiException(/** LANG */'不存在此管理员账号，请重试');
        }
        return $this->getDetail($item['admin_id']);
    }
    /**
     * 会员登录操作
     *
     * @param int $admin_id
     * @param bool $token_login
     * @return array
     */
    public function setLogin(int $admin_id, bool $form_login = true): bool
    {
        if (empty($admin_id)) {
            throw new ApiException(/** LANG */'#adminId错误');
        }
        $user = $this->getDetail($admin_id);
        request()->adminUid = $user['admin_id'];
        request()->merchantId = $user['merchant_id'];
        request()->adminType = $user['admin_type'];
        request()->suppliersId = $user['suppliers_id'];
        request()->authList = $user['auth_list'] ?? [];
        if ($user['admin_type'] == 'shop') {
            request()->shopIds = Shop::where('merchant_id', $user['merchant_id'])->column('shop_id');
            request()->shopId = request()->header('X-Shop-Id', 0);
            if (!in_array(request()->shopId, request()->shopIds)) {
                throw new ApiException('非法请求');
            }
        } elseif ($user['admin_type'] == 'admin') {
            request()->shopId = 0;
        }
        if ($form_login) {
            AdminLog::add('管理员登录:' . $user['username']);
        }
        return true;
    }

    /**
     * 更新单个字段
     *
     * @param int $id
     * @param array $data
     * @return bool
     * @throws ApiException
     */
    public function updateAdminUserField(int $id, array $data):bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = AdminUser::where('admin_id', $id)->save($data);
        AdminLog::add('更新管理员:' . $this->getName($id));
        return $result !== false;
    }

    /**
     * 获取角色列表
     * @return array
     */
    public function getRoleList()
    {
        $list = AdminRole::where("role_id", "<>", 2)->field("role_id,role_name")->select();
        $list = empty($list) ? [] : $list->toArray();
        return $list;
    }

    /**
     * 个人中心管理员账号修改
     * @param array $data
     * @return bool
     * @throws ApiException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function modifyManageAccounts(array $data):bool
    {
        $admin_user_info = AdminUser::find($data["admin_uid"]);
        $admin_user_info = !empty($admin_user_info) ? $admin_user_info->toArray() : [];
        // 管理员账号修改
        $arr = [];
        switch ($data["modify_type"]) {
            case 1:
                // 修改个人信息
                $arr = [
                    "avatar" => $data["avatar"],
                    "email" => $data["email"],
                ];
                break;
            case 2:
                // 修改密码
                if (!empty($data["old_password"])) {
                    if (!password_verify($data["old_password"], $admin_user_info['password'])) {
                        throw new ApiException(/** LANG */'原密码不正确');
                    }
                }
                if (!empty($data["password"])) {
                    $arr["password"] = password_hash($data["password"], PASSWORD_DEFAULT);
                }
                if ($data["password"] != $data["pwd_confirm"]) {
                    throw new ApiException(/** LANG */'密码不一致');
                }
                break;
            case 3:
                // 修改手机号
                if (!empty($data["mobile"])) {
                    $arr["mobile"] = $data["mobile"];
                }

                if (empty($data["code"])) {
                    throw new ApiException(/** LANG */'请输入验证码');
                }
                if (app(SmsService::class)->checkCode($data["mobile"], $data["code"]) == false) {
                    throw new ApiException(/** LANG */'短信验证码错误或已过期，请重试');
                }
                break;
        }
        $result = AdminUser::where("admin_id", $data["admin_uid"])->save($arr);
        return $result !== false;
    }

}
