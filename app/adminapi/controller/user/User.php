<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 会员
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\adminapi\controller\user;

use app\adminapi\AdminBaseController;
use app\service\api\admin\finance\UserBalanceLogService;
use app\service\api\admin\user\UserGrowthPointsLogService;
use app\service\api\admin\user\UserPointsLogService;
use app\service\api\admin\user\UserRankService;
use app\service\api\admin\user\UserService;
use think\App;

/**
 * 会员控制器
 */
class User extends AdminBaseController
{
    protected UserService $userService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param UserService $userService
     */
    public function __construct(App $app, UserService $userService)
    {
        parent::__construct($app);
        $this->userService = $userService;
    }

    /**
     * 列表页面
     *
     * @return \think\Response
     */
    public function list(): \think\Response
    {
        $filter = $this->request->only([
            'keyword' => '',
            'from_tag/d' => 0, // 来源
            'rank_id/d' => 0, // 会员等级
            'balance' => '', // 可用金额
            'points_gt' => '', // 积分大于
            'points_lt' => '', // 积分小于
            'page/d' => 1,
            'size/d' => 15,
            'sort_field' => 'user_id',
            'sort_order' => 'desc',
            'is_page/d' => 0, // 是否分页
        ], 'get');

        $filterResult = $this->userService->getFilterResult($filter);
        $total = $this->userService->getFilterCount($filter);

        return $this->success([
            'filter_result' => $filterResult,
            'filter' => $filter,
            'total' => $total,
        ]);
    }

    /**
     * 详情
     *
     * @return \think\Response
     */
    public function detail(): \think\Response
    {
        $id = input('id/d', 0);
        $item = $this->userService->getDetail($id);
        $item["password"] = '';
        // 会员等级
        $rank_list = app(UserRankService::class)->getUserRankList();
        return $this->success([
            'item' => $item,
            'rank_list' => $rank_list,
        ]);
    }

    /**
     * 执行添加操作
     *
     * @return \think\Response
     */
    public function create(): \think\Response
    {
        $id = input('id/d', 0);
        $data = $this->request->only([
            'user_id' => $id,
            'username' => '',
            'avatar' => '',
            'mobile' => '',
            'email' => '',
            'password' => '',
            'pwd_confirm' => '',
            'rank_id/d' => 0,
        ], 'post');

        $result = $this->userService->updateUser($id, $data, true);
        if ($result) {
            return $this->success('会员添加成功');
        } else {
            return $this->error('会员更新失败');
        }
    }

    /**
     * 执行更新操作
     *
     * @return \think\Response
     */
    public function update(): \think\Response
    {
        $id = input('id/d', 0);
        $data = $this->request->only([
            'user_id' => $id,
            'username' => '',
            'avatar' => '',
            'mobile' => '',
            'email' => '',
            'password' => '',
            'pwd_confirm' => '',
            'rank_id/d' => 0,
        ], 'post');

        $result = $this->userService->updateUser($id, $data, false);
        if ($result) {
            return $this->success('会员更新成功');
        } else {
            return $this->error('会员更新失败');
        }
    }

    /**
     * 更新单个字段
     *
     * @return \think\Response
     */
    public function updateField(): \think\Response
    {
        $id = input('id/d', 0);
        $field = input('field', '');

        if (!in_array($field, ['username', 'sort_order', 'nickname'])) {
            return $this->error('#field 错误');
        }

        $data = [
            'user_id' => $id,
            $field => input('val'),
        ];

        $this->userService->updateUserField($id, $data);

        return $this->success('更新成功');
    }

    /**
     * 删除
     *
     * @return \think\Response
     */
    public function del(): \think\Response
    {
        $id = input('id/d', 0);
        $this->userService->deleteUser($id);
        return $this->success('指定项目已删除');
    }

    /**
     * 批量操作
     *
     * @return \think\Response
     */
    public function batch(): \think\Response
    {
        if (empty(input('ids')) || !is_array(input('ids'))) {
            return $this->error('未选择项目');
        }

        if (input('type') == 'del') {
            foreach (input('ids') as $key => $id) {
                $id = intval($id);
                $this->userService->deleteUser($id);
            }
            return $this->success('批量操作执行成功！');
        } else {
            return $this->error('#type 错误');
        }
    }

    /**
     * 资金明细
     * @return \think\Response
     */
    public function userFundDetail(): \think\Response
    {
        $user_id = input('id/d', 0);
        $filter = $this->request->only([
            'page/d' => 1,
            'size/d' => 15,
            "user_id" => $user_id,
            "from_tag/d" => 1,
            'keyword' => '',
            'sort_field' => '',
            'sort_order' => '',
        ], 'get');
        switch ($filter['from_tag']) {
            case 1:
                //可用资金
                $filter['balance'] = true;
                $filterResult = app(UserBalanceLogService::class)->getFilterResult($filter);
                $total = app(UserBalanceLogService::class)->getFilterCount($filter);
                break;
            case 2:
                //冻结资金
                $filter['frozen_balance'] = true;
                $filterResult = app(UserBalanceLogService::class)->getFilterResult($filter);
                $total = app(UserBalanceLogService::class)->getFilterCount($filter);
                break;
            case 3:
                //成长积分
                $filterResult = app(UserGrowthPointsLogService::class)->getFilterResult($filter);
                $total = app(UserGrowthPointsLogService::class)->getFilterCount($filter);
                break;
            case 4:
                //消费积分
                $filterResult = app(UserPointsLogService::class)->getFilterResult($filter);
                $total = app(UserPointsLogService::class)->getFilterCount($filter);
                break;
            default:
                //可用资金
                $filter['balance'] = true;
                $filterResult = app(UserBalanceLogService::class)->getFilterResult($filter);
                $total = app(UserBalanceLogService::class)->getFilterCount($filter);
                break;
        }
        return $this->success([
            'filter_result' => $filterResult,
            'filter' => $filter,
            'total' => $total,
        ]);
    }

    /**
     * 资金管理
     * @return \think\Response
     * @throws \exceptions\ApiException
     */
    public function fundManagement(): \think\Response
    {
        $user_id = input('id/d', 0);
        $data = $this->request->only([
            "change_desc" => "",
            "type_balance/d" => 1,
            "balance" => 0,
            "type_frozen_balance" => 1,
            "frozen_balance" => 0,
            "type_points" => 1,
            "points" => 0,
            "type_growth_points" => 1,
            "growth_points" => 0,
        ], 'post');
        $result = $this->userService->fundManagement($user_id, $data);
        return $result ? $this->success(/** LANG */'会员帐户变动明细更新成功') : $this->error(/** LANG */'操作失败');
    }
}
