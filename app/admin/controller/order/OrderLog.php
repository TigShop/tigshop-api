<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 订单日志
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\admin\controller\order;

use app\admin\AdminBaseController;
use app\service\api\admin\order\OrderLogService;
use think\App;

/**
 * 订单日志控制器
 */
class OrderLog extends AdminBaseController
{
    protected OrderLogService $orderLogService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param OrderLogService $orderLogService
     */
    public function __construct(App $app, OrderLogService $orderLogService)
    {
        parent::__construct($app);
        $this->orderLogService = $orderLogService;
        $this->checkAuthor('orderLogManage'); //权限检查
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
            'order_id/d' => 0,
            'page/d' => 1,
            'size/d' => 15,
            'sort_field' => 'log_id',
            'sort_order' => 'desc',
        ], 'get');

        $filterResult = $this->orderLogService->getFilterResult($filter);
        $total = $this->orderLogService->getFilterCount($filter);

        return $this->success([
            'filter_result' => $filterResult,
            'filter' => $filter,
            'total' => $total,
        ]);
    }

    /**
     * 详情页面
     *
     * @return \think\Response
     */
    public function detail(): \think\Response
    {

        $id = input('id/d', 0);
        $item = $this->orderLogService->getDetail($id);
        return $this->success([
            'item' => $item,
        ]);
    }

    /**
     * 执行添加或更新操作
     *
     * @return \think\Response
     */
    public function update(): \think\Response
    {
        $id = input('id/d', 0);
        $data = $this->request->only([
            'log_id' => $id,
            'description' => '',
            'order_id/d' => 0,
            'order_sn' => '',
            'store_id/d' => 0,
        ], 'post');

        $result = $this->orderLogService->addOrderLog($id, $data);
        if ($result) {
            return $this->success('订单日志添加成功');
        } else {
            return $this->error('订单日志更新失败');
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

        if (!in_array($field, ['description', 'is_show', 'sort_order'])) {
            return $this->error('#field 错误');
        }

        $data = [
            'log_id' => $id,
            $field => input('val'),
        ];

        $this->orderLogService->addOrderLog($id, $data);

        return $this->success('更新成功');
    }

}
