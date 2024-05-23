<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 计划任务
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\admin\controller\setting;

use app\admin\AdminBaseController;
use app\service\api\admin\setting\CronsService;
use app\validate\setting\CronsValidate;
use exceptions\ApiException;
use think\App;
use think\exception\ValidateException;
use think\facade\Db;
use think\Response;

/**
 * 计划任务控制器
 */
class Crons extends AdminBaseController
{
    protected CronsService $cronsService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param CronsService $cronsService
     */
    public function __construct(App $app, CronsService $cronsService)
    {
        parent::__construct($app);
        $this->cronsService = $cronsService;
        $this->checkAuthor('cronsManage'); //权限检查
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
            'sort_field' => 'cron_id',
            'sort_order' => 'desc',
        ], 'get');

        $filterResult = $this->cronsService->getFilterResult($filter);
        $total = $this->cronsService->getFilterCount($filter);

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
        $item = $this->cronsService->getDetail($id);
        return $this->success([
            'item' => $item,
        ]);
    }

    /**
     * 获取请求数据
     * @return array
     */
    public function requestData(): array
    {
        $data = $this->request->only([
            'cron_name' => '',
            'cron_sn' => '',
            'cron_desc' => '',
            'last_runtime' => '',
            'next_runtime' => '',
            'cron_type' => '',
            'cron_config' => '',
            'is_enabled' => '',
            'just_once' => '',
            'white_ip_list' => '',
            'sort_order/d' => 50,
        ], 'post');
        return $data;
    }

    /**
     * 添加计划任务
     * @return Response
     */
    public function create(): Response
    {
        $data = $this->requestData();

        try {
            validate(CronsValidate::class)
                ->scene('create')
                ->check($data);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        $result = $this->cronsService->createCrons($data);
        if ($result) {
            return $this->success(/** LANG */'计划任务添加成功');
        } else {
            return $this->error(/** LANG */'计划任务添加失败');
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
        $data = $this->requestData();
        $data["cron_id"] = $id;

        try {
            validate(CronsValidate::class)
                ->scene('update')
                ->check($data);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        $result = $this->cronsService->updateCrons($id, $data);
        if ($result) {
            return $this->success(/** LANG */'计划任务更新成功');
        } else {
            return $this->error(/** LANG */'计划任务更新失败');
        }
    }

    /**
     * 更新单个字段
     *
     * @return Response
     */
    public function updateField(): Response
    {
        $id = input('id/d', 0);
        $field = input('field', '');

        if (!in_array($field, ['cron_name', 'sort_order', 'is_enabled'])) {
            return $this->error(/** LANG */'#field 错误');
        }

        $data = [
            'cron_id' => $id,
            $field => input('val'),
        ];

        $this->cronsService->updateCronsField($id, $data);

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
        $this->cronsService->deleteCrons($id);
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
                    $this->cronsService->deleteCrons($id);
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
