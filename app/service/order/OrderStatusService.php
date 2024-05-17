<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 订单不同状态的操作
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\order;

use app\model\order\Order;
use app\service\BaseService;
use app\validate\order\OrderValidate;

/**
 * 订单服务类
 */
class OrderStatusService extends BaseService
{
    protected Order $orderModel;
    protected OrderValidate $orderValidate;

    public function __construct()
    {
    }

    // 获取当前订单状态下可执行的所有操作
    public function getAvailableActions(Order $order)
    {
        // 初始化所有动作为false
        $actions = [
            'set_confirm' => false, // 确认订单
            'to_pay' => false, // 去支付（会员操作）
            'set_paid' => false, // 设置为已支付
            'set_unpaid' => false, // 设置为未支付
            'cancel_order' => false, // 取消订单
            'del_order' => false, // 删除订单
            'deliver' => false, // 发货
            'confirm_receipt' => false, // 确认收货
            'split_order' => false, // 拆分订单
            'modify_order' => false, // 修改订单
            'rebuy' => false, // 再次购买（会员操作）
            'modify_order_money' => false, // 修改订单金额
            'modify_order_consignee' => false, // 修改收货人信息
            'modify_order_product' => false, // 修改订单商品
            'modify_shipping_info' => false, // 修改配送信息
            'to_aftersales' => false, // 申请售后
            'to_comment' => false, // 评价晒单
        ];

        // 订单状态判断
        switch ($order->order_status) {
            case Order::ORDER_PENDING:
                //待确认，待付款
                $actions['set_confirm'] = true;
                $actions['cancel_order'] = true;
                $actions['modify_order'] = true;
                $actions['modify_shipping_info'] = true;
                $actions['modify_order_money'] = true;
                $actions['set_paid'] = true;
                if (request()->adminUid && $order->pay_type_id != Order::PAY_TYPE_ID_OFFLINE) {
                    //后台用户不允许操作非线下支付订单
                    $actions['set_paid'] = false;
                }
                $actions['to_pay'] = true;
                break;
            case Order::ORDER_CONFIRMED:
                //已确认，待发货
                $actions['split_order'] = true;
                $actions['deliver'] = true;
                if ($order->pay_status == Order::PAYMENT_PAID) {
                } else {
                    $actions['set_paid'] = true;
                    $actions['cancel_order'] = true;
                }
                $actions['to_aftersales'] = !app(AftersalesService::class)->getAfterSalesCount($order->order_id);

                break;
            case Order::ORDER_PROCESSING:
                //已发货，处理中
                $actions['confirm_receipt'] = true;
                $actions['modify_shipping_info'] = true;
                $actions['to_aftersales'] = !app(AftersalesService::class)->getAfterSalesCount($order->order_id);
                break;
            case Order::ORDER_COMPLETED:
                $actions['rebuy'] = true;
                $actions['to_aftersales'] = !app(AftersalesService::class)->getAfterSalesCount($order->order_id);
                if ($order->comment_status == Order::COMMENT_PENDING) {
                    $actions['to_comment'] = true;
                }
                break;
            case Order::ORDER_CANCELLED:
                $actions['rebuy'] = true;
                $actions['del_order'] = true;
                break;
            case Order::ORDER_INVALID:
                $actions['del_order'] = true;
                break;
                // 其他状态...
        }

        // 物流状态判断
        switch ($order->shipping_status) {
            case Order::SHIPPING_PENDING:
                // 待发货状态下的可执行操作
                break;
            case Order::SHIPPING_SENT:
                break;
            case Order::SHIPPING_SHIPPED:
                break;
                // 其他状态...
        }

        // 支付状态判断
        switch ($order->pay_status) {
            case Order::PAYMENT_UNPAID:
                break;
            case Order::PAYMENT_PAID:
                $actions['set_unpaid'] = false; // 已支付订单通常不会设置为未支付
                break;
                // 其他状态...
        }

        // 其他状态相关逻辑...
        if ($order->is_store_splited === 1) {
            $actions['split_order'] = false;
        } else {
            $actions['deliver'] = false;
        }

        if ($order->is_del) {
            $actions['del_order'] = false;
        }

        return $actions;
    }

    /**
     * 获取订单状态进度条相关
     *
     * @param Order $order
     * @return array
     */
    public function getStepStatus(Order $order): array
    {
        // 初始化所有动作为false
        $current = 0;
        $status = 'process';
        $steps = [];
        $steps[0] = [
            'title' => '提交订单',
            'description' => $order->add_time,
        ];

        $steps[1] = [
            'title' => Order::PAY_STATUS_MAP[$order->pay_status],
            'description' => '',
        ];
        // 支付状态判断
        switch ($order->pay_status) {
            case Order::PAYMENT_UNPAID:
                $current = 1;
                break;
            case Order::PAYMENT_PAID:
                $current = 2;
                $steps[1]['description'] = $order->pay_time;
                break;
        }
        $steps[2] = [
            'title' => Order::SHIPPING_STATUS_MAP[$order->shipping_status],
            'description' => '',
        ];
        // 物流状态判断
        switch ($order->shipping_status) {
            case Order::SHIPPING_PENDING:
                break;
            case Order::SHIPPING_SENT:
                $current = 2;
                $steps[2]['description'] = $order->shipping_time;
                break;
            case Order::SHIPPING_SHIPPED:
                $current = 2;
                $steps[2]['description'] = $order->received_time;
                $status = 'finish';
                break;
                // 其他状态...
        }

        // 订单状态判断
        switch ($order->order_status) {
            case Order::ORDER_PENDING:
                break;
            case Order::ORDER_CONFIRMED:
                break;
            case Order::ORDER_PROCESSING:
                break;
            case Order::ORDER_COMPLETED:
                break;
            case Order::ORDER_CANCELLED:
                $current = 1;
                $steps[1] = [
                    'title' => Order::ORDER_STATUS_MAP[Order::ORDER_CANCELLED],
                    'description' => '',
                ];
                unset($steps[2]);
                $status = 'error';
                break;
            case Order::ORDER_INVALID:
                $current = 1;
                $steps[1]['description'] = '';
                $status = 'error';
                break;
        }

        return [
            'current' => $current,
            'status' => $status,
            'steps' => $steps,
        ];
    }
}
