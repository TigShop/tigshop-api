<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 充值申请
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\adminapi\controller\finance;

use app\adminapi\AdminBaseController;
use app\service\api\admin\finance\UserRechargeOrderService;
use exceptions\ApiException;
use think\App;
use think\facade\Db;
use think\Response;

/**
 * 充值申请控制器
 */
class UserRechargeOrder extends AdminBaseController
{
    protected UserRechargeOrderService $userRechargeOrderService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param UserRechargeOrderService $userRechargeOrderService
     */
    public function __construct(App $app, UserRechargeOrderService $userRechargeOrderService)
    {
        parent::__construct($app);
        $this->userRechargeOrderService = $userRechargeOrderService;
        $this->checkAuthor('userRechargeOrderManage'); //权限检查
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
            'page/d' => 1,
            'size/d' => 15,
            'sort_field' => 'order_id',
            'sort_order' => 'desc',
        ], 'get');

        $filterResult = $this->userRechargeOrderService->getFilterResult($filter);
        $total = $this->userRechargeOrderService->getFilterCount($filter);

        return $this->success([
            'filter_result' => $filterResult,
            'filter' => $filter,
            'total' => $total,
        ]);
    }

    /**
     * 详情
     *
     * @return Response
     */
    public function detail(): Response
    {
        $id = input('id/d', 0);
        $item = $this->userRechargeOrderService->getDetail($id);
        return $this->success([
            'item' => $item,
        ]);
    }

    /**
     * 添加
     * @return Response
     */
    public function create(): Response
    {
        $data = $this->request->only([
            'user_id/d' => 0,
            'amount' => 0,
            'postscript' => '',
            'status/d' => 0,
        ], 'post');

        $result = $this->userRechargeOrderService->createUserRechargeOrder($data);
        if ($result) {
            return $this->success(/** LANG */'充值申请添加成功');
        } else {
            return $this->error(/** LANG */'充值申请添加失败');
        }
    }

    /**
     * 执行更新操作
     *
     * @return Response
     */
    public function update(): Response
    {
        $id = input('id/d', 0);
        $data = $this->request->only([
            'order_id' => $id,
            'postscript' => '',
            'status/d' => 0,
        ], 'post');

        $result = $this->userRechargeOrderService->updateUserRechargeOrder($id, $data);
        if ($result) {
            return $this->success(/** LANG */'充值申请更新成功');
        } else {
            return $this->error(/** LANG */'充值申请更新失败');
        }
    }

    /**
     * 删除
     * @return Response
     */
    public function del(): Response
    {
        $id = input('id/d', 0);
        $this->userRechargeOrderService->deleteUserRechargeOrder($id);
        return $this->success(/** LANG */'指定项目已删除');
    }

    /**
     * 批量操作
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
                    $this->userRechargeOrderService->deleteUserRechargeOrder($id);
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
