<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 订单
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\adminapi\controller\order;

use app\adminapi\AdminBaseController;
use app\service\api\admin\order\OrderDetailService;
use app\service\api\admin\order\OrderLogService;
use app\service\api\admin\order\OrderService;
use think\App;

/**
 * 订单控制器
 */
class Order extends AdminBaseController
{
    protected OrderService $orderService;
    protected OrderLogService $orderLogService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param OrderService $orderService
     */
    public function __construct(App $app, OrderService $orderService, OrderLogService $orderLogService)
    {
        parent::__construct($app);
        $this->orderService = $orderService;
        $this->orderLogService = $orderLogService;
        //$this->checkAuthor('orderManage'); //权限检查
    }

    /**
     * 列表页面
     *
     * @return \think\Response
     */
    public function list(): \think\Response
    {
        $filter = $this->request->only([
            'is_del/d' => -1,
            'keyword' => '',
            'user_id/d' => 0,
            'order_status/d' => -1,
            'shop_id' => 0,
            'pay_status/d' => -1,
            'shipping_status/d' => -1,
            'address' => '',
            'email' => '',
            'mobile' => '',
            'logistics_id/d' => 0,
            "add_start_time" => "",
            "add_end_time" => "",
            'comment_status/d' => -1,
            'page/d' => 1,
            'size/d' => 15,
            'sort_field' => 'order_id',
            'sort_order' => 'desc',
        ], 'get');

        $filter['shop_id'] = $this->shopId;

        $filterResult = $this->orderService->getFilterResult($filter);
        $total = $this->orderService->getFilterCount($filter);
        return $this->success([
            'filter_result' => $filterResult,
            'filter' => $filter,
            'total' => $total,
        ]);
    }

    /**
     * 订单详情
     *
     * @return \think\Response
     */
    public function detail(): \think\Response
    {
        $id = input('id/d', 0);
        $item = $this->orderService->getDetail($id);
        return $this->success([
            'item' => $item,
        ]);
    }

    //订单设置为已确认
    public function setConfirm(): \think\Response
    {
        $id = input('id/d', 0);
        $this->orderService->setOrderConfirm($id);
        return $this->success('订单状态已更新');
    }

    //订单拆分
    public function splitStoreOrder(): \think\Response
    {
        $id = input('id/d', 0);
        $this->orderService->splitStoreOrder($id);
        return $this->success('订单已拆分');
    }

    //订单设置为已支付
    public function setPaid(): \think\Response
    {
        $id = input('id/d', 0);
        $orderDetail = app(OrderDetailService::class)->setOrderId($id);
        $orderDetail->setOfflinePaySuccess();
        return $this->success('订单状态已更新');
    }

    //取消订单
    public function cancelOrder(): \think\Response
    {
        $id = input('id/d', 0);
        $this->orderService->cancelOrder($id);
        return $this->success('订单已取消');
    }

    //删除订单
    public function delOrder(): \think\Response
    {
        $id = input('id/d', 0);
        $this->orderService->delOrder($id);
        return $this->success('订单已删除');
    }

    //修改订单金额
    public function modifyMoney(): \think\Response
    {
        $id = input('id/d', 0);
        $data = $this->request->only([
            'shipping_fee/f' => 0.00,
            'invoice_fee/f' => 0.00,
            'service_fee/f' => 0.00,
            'discount_amount/f' => 0.00,
        ], 'post');
        $this->orderService->modifyOrderMoney($id, $data);
        return $this->success('订单金额已修改');
    }

    //修改收货人信息
    public function modifyConsignee(): \think\Response
    {
        $id = input('id/d', 0);
        $data = $this->request->only([
            'consignee' => '',
            'mobile' => '',
            'telephone' => '',
            'email' => '',
            'postcode' => '',
            'region_ids/a' => [],
            'address' => '',
        ], 'post');
        $this->orderService->modifyOrderConsignee($id, $data);
        return $this->success('订单收货人信息已修改');
    }

    /**
     * 确认收货
     * @return \think\Response
     * @throws \exceptions\ApiException
     */
    public function confirmReceipt(): \think\Response
    {
        $id = input('id/d', 0);
        $this->orderService->confirmReceipt($id, null);
        return $this->success('订单已确认收货');
    }

    //修改配送信息
    public function modifyShipping(): \think\Response
    {
        $id = input('id/d', 0);
        $data = $this->request->only([
            'shipping_method/d' => 0,
            'logistics_id/d' => 0,
            'tracking_no' => '',
        ], 'post');
        $this->orderService->modifyOrderShipping($id, $data);
        return $this->success('订单收货人信息已修改');
    }

    //修改商品信息
    public function modifyProduct(): \think\Response
    {
        $id = input('id/d', 0);
        $data = input('items', []);
        $this->orderService->modifyOrderProduct($id, $data);
        return $this->success('订单商品信息已更新');
    }

    // 添加商品时获取商品信息
    public function getAddProductInfo(): \think\Response
    {
        $ids = input('ids', []);
        $product_items = $this->orderService->getAddProductInfoByIds($ids);
        return $this->success([
            'product_items' => $product_items,
        ]);
    }

    // 设置商家备注
    public function setAdminNote(): \think\Response
    {
        $id = input('id/d', 0);
        $admin_note = input('admin_note', '');
        $this->orderService->setAdminNote($id, $admin_note);
        return $this->success('订单商家备注已更新');
    }

    // 发货
    public function deliver(): \think\Response
    {
        $id = input('id/d', 0);
        $deliver_data = input('deliver_data/a', []);
        $shipping_method = input('shipping_method/d', 1);
        $logistics_id = input('logistics_id/d', 0);
        $tracking_no = input('tracking_no', '');
        $this->orderService->deliverOrder($id, $deliver_data, $shipping_method, $logistics_id, $tracking_no);
        return $this->success('订单商品发货成功');
    }

    //打印订单
    public function orderPrint(): \think\Response
    {
        $id = input('id/d', 0);
        $order_print = $this->orderService->getOrderPrintInfo($id);
        return $this->success([
            'order_print' => $order_print,
        ]);
    }

    /**
     * 订单导出标签列表
     * @return \think\Response
     */
    public function getExportItemList(): \think\Response
    {
        $export_item_list = $this->orderService->getExportItemList();
        return $this->success([
            'export_item_list' => $export_item_list,
        ]);
    }

    /**
     * 订单导出存的标签
     * @return \think\Response
     */
    public function saveExportItem(): \think\Response
    {
        $order_export = input('export_item', []);
        $result = $this->orderService->saveExportItem($order_export);
        return $result ? $this->success('保存成功') : $this->error('保存失败');
    }

    // 标签详情
    public function exportItemInfo(): \think\Response
    {
        $item = $this->orderService->getExportItemInfo();
        return $this->success([
            'item' => $item,
        ]);
    }

    //订单导出
    public function orderExport(): \think\Response
    {
        $filter = $this->request->only([
            'is_del/d' => -1,
            'keyword' => '',
            'user_id/d' => 0,
            'order_status/d' => -1,
            'shop_id' => 0,
            'pay_status/d' => -1,
            'shipping_status/d' => -1,
            'comment_status/d' => -1,
            'address' => '',
            'email' => '',
            'mobile' => '',
            'logistics_id/d' => 0,
            'add_start_time' => "",
            'add_end_time' => "",
            'page/d' => 1,
            'size/d' => 99999,
            'sort_field' => 'order_id',
            'sort_order' => 'desc',
        ], 'get');
        $filter['shop_id'] = $this->shopId;

        //导出栏目
        $exportItem = input('export_item', []);
        if(empty($exportItem))
        {
            return $this->error('导出栏目不能为空！');
        }

        $filterResult = $this->orderService->getFilterList($filter)->toArray();
        $result = $this->orderService->orderExport($filterResult,$exportItem);
        return $result ? $this->success("导出成功") : $this->error('导出失败');
    }

}
