<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 发票申请
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\admin\controller\finance;

use app\admin\AdminBaseController;
use app\common\exceptions\ApiException;
use app\service\api\admin\finance\OrderInvoiceService;
use app\validate\finance\OrderInvoiceValidate;
use think\App;
use think\exception\ValidateException;
use think\facade\Db;
use think\Response;

/**
 * 发票申请控制器
 */
class OrderInvoice extends AdminBaseController
{
    protected OrderInvoiceService $orderInvoiceService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param OrderInvoiceService $orderInvoiceService
     */
    public function __construct(App $app, OrderInvoiceService $orderInvoiceService)
    {
        parent::__construct($app);
        $this->orderInvoiceService = $orderInvoiceService;
        $this->checkAuthor('orderInvoiceManage'); //权限检查
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
            'invoice_type/d' => 0,
            'status/d' => -1,
            'store_type/d' => 0,
            'store_id/d' => -1,
            'page/d' => 1,
            'size/d' => 15,
            'sort_field' => 'id',
            'sort_order' => 'desc',
        ], 'get');

        $filterResult = $this->orderInvoiceService->getFilterResult($filter);
        $total = $this->orderInvoiceService->getFilterCount($filter);

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
        $item = $this->orderInvoiceService->getDetail($id);
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
            'status/d' => 0,
            'amount' => '0.00',
            'apply_reply' => '',
        ], 'post');

        try {
            validate(OrderInvoiceValidate::class)
                ->scene('create')
                ->check($data);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        $result = $this->orderInvoiceService->createOrderInvoice($data);
        if ($result) {
            return $this->success(/** LANG */'发票申请添加成功');
        } else {
            return $this->error(/** LANG */'发票申请更新失败');
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
            'id' => $id,
            'status/d' => 0,
            'amount' => '0.00',
            'apply_reply' => '',
        ], 'post');

        try {
            validate(OrderInvoiceValidate::class)
                ->scene('update')
                ->check($data);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        $result = $this->orderInvoiceService->updateOrderInvoice($id, $data);
        if ($result) {
            return $this->success(/** LANG */'发票申请更新成功');
        } else {
            return $this->error(/** LANG */'发票申请更新失败');
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

        if (!in_array($field, ['company_name', 'sort_order', 'is_show'])) {
            return $this->error(/** LANG */'#field 错误');
        }

        $data = [
            'id' => $id,
            $field => input('val'),
        ];

        $this->orderInvoiceService->updateOrderInvoiceField($id, $data);

        return $this->success(/** LANG */'更新成功');
    }

    /**
     * 删除
     * @return Response
     */
    public function del(): Response
    {
        $id = input('id/d', 0);
        $this->orderInvoiceService->deleteOrderInvoice($id);
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
                    $this->orderInvoiceService->deleteOrderInvoice($id);
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
