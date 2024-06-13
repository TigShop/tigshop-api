<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 提现申请
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\adminapi\controller\finance;

use app\adminapi\AdminBaseController;
use app\service\api\admin\finance\UserWithdrawApplyService;
use app\validate\finance\UserWithdrawApplyValidate;
use exceptions\ApiException;
use think\App;
use think\exception\ValidateException;
use think\facade\Db;
use think\Response;

/**
 * 提现申请控制器
 */
class UserWithdrawApply extends AdminBaseController
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
        $this->userWithdrawApplyService = $userWithdrawApplyService;
        $this->checkAuthor('userWithdrawApplyManage'); //权限检查
    }

    /**
     * 列表页面
     *
     * @return Response
     */
    public function list(): Response
    {
        $filter = $this->request->only([
            'keyword' => '',
            'status/d' => -1,
            'user_id/d' => 0,
            'page/d' => 1,
            'size/d' => 15,
            'sort_field' => 'id',
            'sort_order' => 'desc',
        ], 'get');

        $filterResult = $this->userWithdrawApplyService->getFilterResult($filter);
        $total = $this->userWithdrawApplyService->getFilterCount($filter);

        return $this->success([
            'filter_result' => $filterResult,
            'filter' => $filter,
            'total' => $total,
        ]);
    }

    /**
     * 详情
     * @return Response
     */
    public function detail(): Response
    {
        $id = input('id/d', 0);
        $item = $this->userWithdrawApplyService->getDetail($id);
        return $this->success([
            'item' => $item,
        ]);
    }

    /**
     * 添加
     * @return Response
     * @throws \exceptions\ApiException
     */
    public function create(): Response
    {
        $data = $this->request->only([
            'user_id/d' => 0,
            'amount' => '',
            'postscript' => '',
            'status/d' => 0,
            'account_data' => [],
        ], 'post');

        try {
            validate(UserWithdrawApplyValidate::class)
                ->scene('create')
                ->check($data);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        $result = $this->userWithdrawApplyService->createUserWithdrawApply($data);
        if ($result) {
            return $this->success(/** LANG */'提现申请添加成功');
        } else {
            return $this->error(/** LANG */'提现申请添加失败');
        }
    }

    /**
     * 执行更新操作
     * @return Response
     */
    public function update(): Response
    {
        $id = input('id/d', 0);
        $data = $this->request->only([
            'id' => $id,
            'postscript' => '',
            'status/d' => 0,
        ], 'post');

        try {
            validate(UserWithdrawApplyValidate::class)
                ->scene('update')
                ->check($data);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        $result = $this->userWithdrawApplyService->updateUserWithdrawApply($id, $data);
        if ($result) {
            return $this->success(/** LANG */'提现申请更新成功');
        } else {
            return $this->error(/** LANG */'提现申请更新失败');
        }
    }

    /**
     * 删除
     *
     * @return Response
     */
    public function del(): Response
    {
        $id = input('id/d', 0);
        $this->userWithdrawApplyService->deleteUserWithdrawApply($id);
        return $this->success(/** LANG */'指定项目已删除');
    }

    /**
     * 批量操作
     *
     * @return Response
     */
    public function batch(): Response
    {
        if (empty(input('ids')) || !is_array(input('ids'))) {
            return $this->error(/** LANG */'未选择项目');
        }

        if (input('type') == 'del') {
            try {
                //批量操作一定要事务
                Db::startTrans();
                foreach (input('ids') as $key => $id) {
                    $id = intval($id);
                    $this->userWithdrawApplyService->deleteUserWithdrawApply($id);
                }
                Db::commit();
            } catch (\Exception $exception) {
                Db::rollback();
                throw new ApiException($exception->getMessage());
            }

            return $this->success(/** LANG */'批量操作执行成功！');
        } else {
            return $this->error(/** LANG */'#type 错误');
        }
    }
}
