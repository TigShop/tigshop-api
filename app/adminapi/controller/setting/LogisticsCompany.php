<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 物流公司
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\adminapi\controller\setting;

use app\adminapi\AdminBaseController;
use app\service\api\admin\setting\LogisticsCompanyService;
use app\validate\setting\LogisticsCompanyValidate;
use exceptions\ApiException;
use think\App;
use think\exception\ValidateException;
use think\facade\Db;
use think\Response;

/**
 * 物流公司控制器
 */
class LogisticsCompany extends AdminBaseController
{
    protected LogisticsCompanyService $logisticsCompanyService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param LogisticsCompanyService $logisticsCompanyService
     */
    public function __construct(App $app, LogisticsCompanyService $logisticsCompanyService)
    {
        parent::__construct($app);
        $this->logisticsCompanyService = $logisticsCompanyService;
        $this->checkAuthor('logisticsCompanyManage'); //权限检查
    }

    /**
     * 分页列表页面
     * @return Response
     */
    public function list(): Response
    {
        $filter = $this->request->only([
            'keyword' => '',
            'page/d' => 1,
            'size/d' => 15,
            'paging' => true,
            'sort_field' => 'logistics_id',
            'sort_order' => 'desc',
        ], 'get');

        $filterResult = $this->logisticsCompanyService->getFilterResult($filter);
        $total = $this->logisticsCompanyService->getFilterCount($filter);

        return $this->success([
            'filter_result' => $filterResult,
            'filter' => $filter,
            'total' => $total,
        ]);
    }

    /**
     * 列表页面
     * @return Response
     */
    public function getAll(): Response
    {
        $filter = $this->request->only([
            'keyword' => '',
            'logistics_id/d' => 0,
            'paging' => false,
            'sort_field' => 'logistics_id',
            'sort_order' => 'desc',
        ], 'get');

        $filterResult = $this->logisticsCompanyService->getFilterResult($filter);

        return $this->success([
            'filter_result' => $filterResult,
            'filter' => $filter,
        ]);
    }

    /**
     * 详情
     * @return Response
     */
    public function detail(): Response
    {
        $id = input('id/d', 0);
        $item = $this->logisticsCompanyService->getDetail($id);
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
            'logistics_name' => '',
            'logistics_code' => '',
            'logistics_desc' => '',
            'is_show' => '',
            'sort_order/d' => 50,
        ], 'post');

        try {
            validate(LogisticsCompanyValidate::class)
                ->scene('create')
                ->check($data);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        $result = $this->logisticsCompanyService->createLogisticsCompany($data);
        if ($result) {
            return $this->success(/** LANG */'物流公司添加成功');
        } else {
            return $this->error(/** LANG */'物流公司更新失败');
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
            'logistics_id' => $id,
            'logistics_name' => '',
            'logistics_code' => '',
            'logistics_desc' => '',
            'is_show' => '',
            'sort_order/d' => 50,
        ], 'post');

        try {
            validate(LogisticsCompanyValidate::class)
                ->scene('update')
                ->check($data);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        $result = $this->logisticsCompanyService->updateLogisticsCompany($id, $data);
        if ($result) {
            return $this->success(/** LANG */'物流公司更新成功');
        } else {
            return $this->error(/** LANG */'物流公司更新失败');
        }
    }

    /**
     * 更新单个字段
     * @return Response
     */
    public function updateField(): Response
    {
        $id = input('id/d', 0);
        $field = input('field', '');

        if (!in_array($field, ['logistics_name', 'sort_order', "is_show", "logistics_code", "logistics_desc"])) {
            return $this->error(/** LANG */'#field 错误');
        }

        $data = [
            'logistics_id' => $id,
            $field => input('val'),
        ];

        $this->logisticsCompanyService->updateLogisticsCompanyField($id, $data);

        return $this->success(/** LANG */'更新成功');
    }

    /**
     * 删除
     *
     * @return Response
     */
    public function del(): Response
    {
        $id = input('id/d', 0);
        $this->logisticsCompanyService->deleteLogisticsCompany($id);
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
                    $this->logisticsCompanyService->deleteLogisticsCompany($id);
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
