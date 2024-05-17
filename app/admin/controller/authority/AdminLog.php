<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 管理员日志
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\admin\controller\authority;

use app\admin\AdminBaseController;
use app\common\exceptions\ApiException;
use app\service\authority\AdminLogService;
use think\App;
use think\facade\Db;
use think\Response;

/**
 * 管理员日志控制器
 */
class AdminLog extends AdminBaseController
{
    protected AdminLogService $adminLogService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param AdminLogService $adminLogService
     */
    public function __construct(App $app, AdminLogService $adminLogService)
    {
        parent::__construct($app);
        $this->adminLogService = $adminLogService;
        $this->checkAuthor('adminLogManage'); //权限检查
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
            'user_id/d' => 0,
            'page/d' => 1,
            'size/d' => 15,
            'sort_field' => 'log_id',
            'sort_order' => 'desc',
        ], 'get');

        $filterResult = $this->adminLogService->getFilterResult($filter);
        $total = $this->adminLogService->getFilterCount($filter);

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
        $this->adminLogService->deleteAdminLog($id);
        return $this->success('指定项目已删除');
    }

    /**
     * 批量操作
     *
     * @return Response
     */
    public function batch(): Response
    {
        if (empty(input('ids')) || !is_array(input('ids'))) {
            return $this->error('未选择项目');
        }

        if (input('type') == 'del') {
            try {
                //批量操作一定要事务
                Db::startTrans();
                foreach (input('ids') as $key => $id) {
                    $id = intval($id);
                    $this->adminLogService->deleteAdminLog($id);
                }
                Db::commit();
            } catch (\Exception $exception) {
                Db::rollback();
                throw new ApiException($exception->getMessage());
            }

            return $this->success('批量操作执行成功！');
        } else {
            return $this->error('#type 错误');
        }
    }
}
