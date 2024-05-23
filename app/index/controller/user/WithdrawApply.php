<?php
//**---------------------------------------------------------------------+
//** 通用接口控制器文件 -- 提现
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\index\controller\user;

use app\index\IndexBaseController;
use app\service\api\admin\finance\UserWithdrawApplyService;
use think\App;
use think\Response;

/**
 * 会员中心提现
 */
class WithdrawApply extends IndexBaseController
{
    protected UserWithdrawApplyService $userWithdrawApplyService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param UserWithdrawApplyService $userWithdrawApplyService
     */
    public function __construct(App $app, UserWithdrawApplyService $userWithdrawApplyService)
    {
        parent::__construct($app);
        $this->checkLogin();
        $this->userWithdrawApplyService = $userWithdrawApplyService;
    }

    /**
     * 提现账号列表
     * @return Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function list(): Response
    {
        $filter = $this->request->only([
            "account_type/d" => 1,
            "account_id/d" => 0,
        ], 'get');

        $filterResult = $this->userWithdrawApplyService->getAccountList($filter, request()->userId);

        return $this->success([
            'filter_result' => $filterResult,
            'filter' => $filter,
        ]);
    }

    /**
     * 添加提现账号
     * @return Response
     */
    public function createAccount(): Response
    {
        $data = $this->request->only([
            "account_type/d" => 1,
            "account_name" => "",
            "account_no" => "",
            "identity" => "",
            "bank_name" => "",
        ], 'post');
        $result = $this->userWithdrawApplyService->addWithdrawAccount($data, request()->userId);
        return $result ? $this->success(/** LANG */"添加成功") : $this->error(/** LANG */"添加失败");
    }

    /**
     * 编辑提现账号
     * @return Response
     */
    public function updateAccount(): Response
    {
        $data = $this->request->only([
            'account_id/d' => 0,
            "account_type/d" => 1,
            "account_name" => "",
            "account_no" => "",
            "identity" => "",
            "bank_name" => "",
        ], 'post');
        $result = $this->userWithdrawApplyService->editWithdrawAccount($data['account_id'], request()->userId, $data);
        return $result ? $this->success(/** LANG */"编辑成功") : $this->error(/** LANG */"编辑失败");
    }

    /**
     * 编辑提现账号
     * @return Response
     */
    public function accountDetail(): Response
    {
        $data = $this->request->only([
            'account_id/d' => 0,
        ]);
        $result = $this->userWithdrawApplyService->withdrawAccountDetail($data['account_id'], request()->userId);
        return $this->success([
            'account_detail' => $result,
        ]);
    }

    /**
     * 删除提现账号
     * @return Response
     */
    public function delAccount(): Response
    {
        $data = $this->request->only([
            'account_id/d' => 0,
        ], 'post');
        $result = $this->userWithdrawApplyService->delWithdrawAccount($data['account_id'], request()->userId);
        return $result ? $this->success(/** LANG */"删除成功") : $this->error(/** LANG */"删除失败");
    }

    /**
     * 添加提现申请
     * @return Response
     * @throws \exceptions\ApiException
     */
    public function apply(): Response
    {
        $data = $this->request->only([
            "amount" => 0,
            "account_data/a" => [],
        ], 'post');
        $result = $this->userWithdrawApplyService->updateUserWithdrawApplyPc($data, request()->userId);
        return $result ? $this->success(/** LANG */"提现申请成功") : $this->error(/** LANG */"提现申请失败");
    }

}
