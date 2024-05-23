<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 余额日志
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\admin\controller\finance;

use app\admin\AdminBaseController;
use app\service\api\admin\finance\UserBalanceLogService;
use exceptions\ApiException;
use think\App;
use think\facade\Db;
use think\Response;

/**
 * 余额日志控制器
 */
class UserBalanceLog extends AdminBaseController
{
    protected UserBalanceLogService $userBalanceLogService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param UserBalanceLogService $userBalanceLogService
     */
    public function __construct(App $app, UserBalanceLogService $userBalanceLogService)
    {
        parent::__construct($app);
        $this->userBalanceLogService = $userBalanceLogService;
        $this->checkAuthor('userBalanceLogManage'); //权限检查
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
            'page/d' => 1,
            'size/d' => 15,
            'sort_field' => 'log_id',
            'sort_order' => 'desc',
        ], 'get');

        $filterResult = $this->userBalanceLogService->getFilterResult($filter);
        $total = $this->userBalanceLogService->getFilterCount($filter);

        return $this->success([
            'filter_result' => $filterResult,
            'filter' => $filter,
            'total' => $total,
        ]);
    }

    /**
     * 删除
     *
     * @return Response
     */
    public function del(): Response
    {
        $id = input('id/d', 0);
        $this->userBalanceLogService->deleteUserBalanceLog($id);
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
                    $this->userBalanceLogService->deleteUserBalanceLog($id);
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
