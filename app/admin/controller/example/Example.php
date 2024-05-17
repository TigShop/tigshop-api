<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 示例模板
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\admin\controller\example;

use app\admin\AdminBaseController;
use app\common\exceptions\ApiException;
use app\service\example\ExampleService;
use app\validate\example\ExampleValidate;
use think\App;
use think\exception\ValidateException;
use think\facade\Db;

/**
 * 示例模板控制器
 */
class Example extends AdminBaseController
{
    protected ExampleService $exampleService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param ExampleService $exampleService
     */
    public function __construct(App $app, ExampleService $exampleService)
    {
        parent::__construct($app);
        $this->exampleService = $exampleService;
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
            'page/d' => 1,
            'size/d' => 15,
            'sort_field' => 'example_id',
            'sort_order' => 'desc',
        ], 'get');

        $filterResult = $this->exampleService->getFilterResult($filter);
        $total = $this->exampleService->getFilterCount($filter);

        return $this->success([
            'filter_result' => $filterResult,
            'filter' => $filter,
            'total' => $total,
        ]);
    }

    /**
     * 配置型
     * @return \think\Response
     */
    public function config(): \think\Response
    {
        return $this->success([
            'status_list' => [
                1 => '待审核',
                2 => '已审核',
            ],
            'type_list' => [
                1 => '帮助文章',
                2 => '资讯文章',
            ],
        ]);
    }

    /**
     *详情数据
     * @return \think\Response
     */
    public function detail(): \think\Response
    {
        $id = input('example_id/d', 0);
        $detail = $this->exampleService->getDetail($id);
        return $this->success([
            'item' => $detail,
        ]);
    }

    /**
     * 新增
     * @return \think\Response
     * @throws ApiException
     */
    public function create(): \think\Response
    {
        $data = $this->request->only([
            'example_name' => '',
            'sort_order/d' => 50,
        ], 'post');
        try {
            validate(ExampleValidate::class)
                ->scene('create')
                ->check($data);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }
        $result = $this->exampleService->create($data);

        return $this->success($result ? '示例模板添加成功' : '示例模板添加失败');
    }

    /**
     * 编辑
     *
     * @return \think\Response
     */
    public function update(): \think\Response
    {
        $data = $this->request->only([
            'example_id' => 0,
            'example_name' => '',
            'sort_order/d' => 50,
        ], 'post');
        try {
            validate(ExampleValidate::class)
                ->scene('update')
                ->check($data);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }
        $result = $this->exampleService->update($data, $data['example_id'], request()->adminUid);
        if ($result) {
            return $this->success('示例模板更新成功');
        } else {
            return $this->error('示例模板更新失败');
        }
    }

    /**
     * 删除
     *
     * @return \think\Response
     */
    public function del(): \think\Response
    {
        $id = input('example_id/d', 0);
        $this->exampleService->del($id, request()->adminUid);
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
            try {
                //批量操作一定要事务
                Db::startTrans();
                foreach (input('ids') as $key => $id) {
                    $id = intval($id);
                    $this->exampleService->del($id, request()->userId);
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
