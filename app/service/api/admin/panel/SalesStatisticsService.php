<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 面板销售统计服务
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\panel;

use app\common\exceptions\ApiException;
use app\common\utils\Time;
use app\model\finance\UserRechargeOrder;
use app\model\order\Order;
use app\model\order\OrderItem;
use app\model\product\Comment;
use app\model\product\Product;
use app\model\user\User;
use app\service\api\admin\BaseService;
use app\service\api\admin\finance\RefundApplyService;
use app\service\api\admin\order\OrderService;
use app\service\api\admin\sys\AccessLogService;

/**
 * @param $data
 * @return array
 * 面板信息类
 */
class SalesStatisticsService extends BaseService
{
    /**
     * 面板控制台 - 控制台数据
     * @return array
     */
    public function getConsoleData(): array
    {
        // 待付款的订单
        $await_pay = Order::awaitPay()->storePlatform()->count();
        // 待发货的订单
        $await_ship = Order::awaitShip()->storePlatform()->count();
        // 待售后的订单
        $await_after_sale = Order::completed()->storePlatform()->count();
        // 待回复的订单
        $await_comment = Comment::awaitComment()->storePlatform()->count();
        $result = [
            'await_pay' => $await_pay,
            'await_ship' => $await_ship,
            'await_after_sale' => $await_after_sale,
            'await_comment' => $await_comment,
        ];
        return $result;
    }

    /**
     * 面板控制台 - 实时数据
     * @return array
     */
    public function getRealTimeData(): array
    {
        // 当天时间段
        $today = Time::getCurrentDatetime("Y-m-d");
        $start_end_time = [$today, $today];
        // 获取环比时间区间
        $prev_date = app(StatisticsUserService::class)->getPrevDate([$today, Time::format(strtotime('+ 1 days'), "Y-m-d")]);
        // 支付金额
        $today_order_amount = Order::payTime($start_end_time)->paid()->storePlatform()->where("is_del", 0)->sum("total_amount");
        $yesterday_order_amount = Order::payTime($prev_date)->paid()->storePlatform()->where("is_del", 0)->sum("total_amount");
        $order_amount_growth_rate = app(StatisticsUserService::class)->getGrowthRate($today_order_amount, $yesterday_order_amount);

        // 访客数
        $today_visit_num = app(AccessLogService::class)->getVisitNum($start_end_time);
        $yesterday_visit_num = app(AccessLogService::class)->getVisitNum($prev_date);
        $visit_growth_rate = app(StatisticsUserService::class)->getGrowthRate($today_visit_num, $yesterday_visit_num);

        //支付买家数
        $today_buyer_num = Order::payTime($start_end_time)->paid()->storePlatform()->where("is_del", 0)->group("user_id")->count();
        $yesterday_buyer_num = Order::payTime($prev_date)->paid()->storePlatform()->where("is_del", 0)->group("user_id")->count();
        $buyer_growth_rate = app(StatisticsUserService::class)->getGrowthRate($today_buyer_num, $yesterday_buyer_num);

        // 浏览量
        $today_view_num = app(AccessLogService::class)->getVisitNum($start_end_time, 1);
        $yesterday_view_num = app(AccessLogService::class)->getVisitNum($prev_date, 1);
        $view_growth_rate = app(StatisticsUserService::class)->getGrowthRate($today_view_num, $yesterday_view_num);

        // 支付订单数
        $today_order_num = Order::payTime($start_end_time)->paid()->storePlatform()->where("is_del", 0)->count();
        $yesterday_order_num = Order::payTime($prev_date)->paid()->storePlatform()->where("is_del", 0)->count();
        $order_growth_rate = app(StatisticsUserService::class)->getGrowthRate($today_order_num, $yesterday_order_num);

        $result = [
            "today_order_amount" => $today_order_amount,
            "order_amount_growth_rate" => $order_amount_growth_rate,
            "today_visit_num" => $today_visit_num,
            "visit_growth_rate" => $visit_growth_rate,
            "today_buyer_num" => $today_buyer_num,
            "buyer_growth_rate" => $buyer_growth_rate,
            "today_view_num" => $today_view_num,
            "view_growth_rate" => $view_growth_rate,
            "today_order_num" => $today_order_num,
            "order_growth_rate" => $order_growth_rate,
        ];
        return $result;
    }

    /**
     * 面板控制台 - 统计图表
     * @return array
     */
    public function getPanelStatisticalData(): array
    {
        // 默认为一个月的数据
        $today = Time::getCurrentDatetime("Y-m-d");
        $month_day = Time::format(Time::monthAgo(1), "Y-m-d");
        $start_end_time = app(StatisticsUserService::class)->getDateRange(0, [$month_day, $today]);

        // 访问统计
        $access_data = app(AccessLogService::class)->getVisitList($start_end_time, 1);

        // 订单统计 -- 订单数量/ 订单金额
        $order_data = Order::field("DATE_FORMAT(FROM_UNIXTIME(pay_time), '%Y-%m-%d') AS period")
            ->field("COUNT(*) AS order_count,SUM(total_amount) AS order_amount")
            ->payTime($start_end_time)
            ->paid()
            ->where("is_del", 0)
            ->storePlatform()
            ->group("period")
            ->select()->toArray();

        // 横轴
        $horizontal_axis = app(StatisticsUserService::class)->getHorizontalAxis(0, $month_day, $today);
        // 访问统计 -- 纵轴
        $longitudinal_axis_access = app(StatisticsUserService::class)->getLongitudinalAxis($horizontal_axis, $access_data, 0, 2);
        // 订单统计 -- 订单数量
        $longitudinal_axis_order_num = app(StatisticsUserService::class)->getLongitudinalAxis($horizontal_axis, $order_data, 0, 3);
        // 订单金额
        $longitudinal_axis_order_amount = app(StatisticsUserService::class)->getLongitudinalAxis($horizontal_axis, $order_data, 0, 7);
        $result = [
            "horizontal_axis" => $horizontal_axis,
            "longitudinal_axis_access" => $longitudinal_axis_access,
            "longitudinal_axis_order_num" => $longitudinal_axis_order_num,
            "longitudinal_axis_order_amount" => $longitudinal_axis_order_amount,
        ];
        return $result;
    }

    /**
     * 销售统计 -- 销售统计展示数据
     * @param array $filter
     * @return array
     * @throws ApiException
     */
    public function getSalesData(array $filter): array
    {
        if (empty($filter["start_end_time"])) {
            throw new ApiException('请选择日期');
        }
        $start_end_time = app(StatisticsUserService::class)->getDateRange($filter["date_type"], $filter["start_end_time"]);
        // 获取环比时间区间
        $prev_date = app(StatisticsUserService::class)->getPrevDate($start_end_time);

        // 商品支付金额
        $product_payment = app(OrderService::class)->getPayMoneyTotal($start_end_time);
        $prev_product_payment = app(OrderService::class)->getPayMoneyTotal($prev_date);
        $product_payment_growth_rate = app(StatisticsUserService::class)->getGrowthRate($product_payment, $prev_product_payment);

        // 商品退款金额
        $product_refund = app(RefundApplyService::class)->getRefundTotal($start_end_time);
        $prev_product_refund = app(RefundApplyService::class)->getRefundTotal($prev_date);
        $product_refund_growth_rate = app(StatisticsUserService::class)->getGrowthRate($product_refund, $prev_product_refund);

        // 充值金额
        $recharge_amount = UserRechargeOrder::paidTime($start_end_time)->paid()->sum('amount');
        $prev_recharge_amount = UserRechargeOrder::paidTime($prev_date)->paid()->sum('amount');
        $recharge_amount_growth_rate = app(StatisticsUserService::class)->getGrowthRate($recharge_amount, $prev_recharge_amount);

        // 营业额
        $turnover = $product_payment + $recharge_amount;
        $prev_turnover = $prev_product_payment + $prev_recharge_amount;
        $turnover_growth_rate = app(StatisticsUserService::class)->getGrowthRate($turnover, $prev_turnover);

        // 余额支付金额
        $balance_payment = app(OrderService::class)->getPayBalanceTotal($start_end_time);
        $prev_balance_payment = app(OrderService::class)->getPayBalanceTotal($prev_date);
        $balance_payment_growth_rate = app(StatisticsUserService::class)->getGrowthRate($balance_payment, $prev_balance_payment);

        $result["sales_data"] = [
            "product_payment" => $product_payment,
            "product_payment_growth_rate" => $product_payment_growth_rate,
            "product_refund" => $product_refund,
            "product_refund_growth_rate" => $product_refund_growth_rate,
            "turnover" => $turnover,
            "turnover_growth_rate" => $turnover_growth_rate,
            "recharge_amount" => $recharge_amount,
            "recharge_amount_growth_rate" => $recharge_amount_growth_rate,
            "balance_payment" => $balance_payment,
            "balance_payment_growth_rate" => $balance_payment_growth_rate,
        ];

        // 获取统计图表数据
        $result["sales_statistics_data"] = $this->getSalesStatisticsData($filter["date_type"], $start_end_time, $filter["statistic_type"]);
        // 导出
        if ($filter["is_export"]) {
            // 导出
            if ($filter["statistic_type"]) {
                app(StatisticsUserService::class)->executeExport($result["sales_statistics_data"], $filter["date_type"], 4);
            } else {
                app(StatisticsUserService::class)->executeExport($result["sales_statistics_data"], $filter["date_type"], 5);
            }
        }
        return $result;
    }

    /**
     * 销售统计 -- 统计图表数据
     * @param int $date_type
     * @param array $start_end_time
     * @param int $statistic_type
     * @return array
     */
    public function getSalesStatisticsData(int $date_type, array $start_end_time, int $statistic_type): array
    {
        list($start_date, $end_date) = $start_end_time;
        // 横轴
        $horizontal_axis = app(StatisticsUserService::class)->getHorizontalAxis($date_type, $start_date, $end_date);
        $order_statistics_list = app(OrderService::class)->getPayMoneyList($start_end_time);
        if ($statistic_type) {
            // 订单金额统计
            $longitudinal_axis = app(StatisticsUserService::class)->getLongitudinalAxis($horizontal_axis, $order_statistics_list, $date_type, 4);
        } else {
            // 订单数统计
            $longitudinal_axis = app(StatisticsUserService::class)->getLongitudinalAxis($horizontal_axis, $order_statistics_list, $date_type, 5);
        }
        $result = [
            "horizontal_axis" => $horizontal_axis,
            "longitudinal_axis" => $longitudinal_axis,
        ];
        return $result;
    }

    /**
     * 销售明细
     * @param array $filter
     * @return array
     * @throws ApiException
     */
    public function getSaleDetail(array $filter): array
    {
        if (empty($filter["start_time"]) || empty($filter["end_time"])) {
            throw new ApiException('请选择日期');
        }
        $start_end_time = [$filter["start_time"], $filter["end_time"]];
        // 获取环比时间区间
        $prev_date = app(StatisticsUserService::class)->getPrevDate($start_end_time);

        // 商品浏览量
        $product_view = app(AccessLogService::class)->getVisitNum($start_end_time, 1, 1);
        $prev_product_view = app(AccessLogService::class)->getVisitNum($prev_date, 1, 1);
        $product_view_growth_rate = app(StatisticsUserService::class)->getGrowthRate($product_view, $prev_product_view);

        // 商品访客数
        $product_visitor = app(AccessLogService::class)->getVisitNum($start_end_time, 0, 1);
        $prev_product_visitor = app(AccessLogService::class)->getVisitNum($prev_date, 0, 1);
        $product_visitor_growth_rate = app(StatisticsUserService::class)->getGrowthRate($product_visitor, $prev_product_visitor);

        // 下单件数
        $order_num = app(OrderService::class)->getOrderTotal($start_end_time);
        $prev_order_num = app(OrderService::class)->getOrderTotal($prev_date);
        $order_num_growth_rate = app(StatisticsUserService::class)->getGrowthRate($order_num, $prev_order_num);

        // 支付金额
        $payment_amount = app(OrderService::class)->getPayMoneyTotal($start_end_time);
        $prev_payment_amount = app(OrderService::class)->getPayMoneyTotal($prev_date);
        $payment_amount_growth_rate = app(StatisticsUserService::class)->getGrowthRate($payment_amount, $prev_payment_amount);

        // 退款金额
        $refund_amount = app(RefundApplyService::class)->getRefundTotal($start_end_time);
        $prev_refund_amount = app(RefundApplyService::class)->getRefundTotal($prev_date);
        $refund_amount_growth_rate = app(StatisticsUserService::class)->getGrowthRate($refund_amount, $prev_refund_amount);

        // 退款件数
        $refund_quantity = app(RefundApplyService::class)->getRefundItemTotal($start_end_time);
        $prev_refund_quantity = app(RefundApplyService::class)->getRefundItemTotal($prev_date);
        $refund_quantity_growth_rate = app(StatisticsUserService::class)->getGrowthRate($refund_quantity, $prev_refund_quantity);

        $result["sales_data"] = [
            "product_view" => $product_view,
            "product_view_growth_rate" => $product_view_growth_rate,
            "product_visitor" => $product_visitor,
            "product_visitor_growth_rate" => $product_visitor_growth_rate,
            "order_num" => $order_num,
            "order_num_growth_rate" => $order_num_growth_rate,
            "payment_amount" => $payment_amount,
            "payment_amount_growth_rate" => $payment_amount_growth_rate,
            "refund_amount" => $refund_amount,
            "refund_amount_growth_rate" => $refund_amount_growth_rate,
            "refund_quantity" => $refund_quantity,
            "refund_quantity_growth_rate" => $refund_quantity_growth_rate,
        ];

        $result["sales_statistics_data"] = $this->getSalesStatisticsDetail($start_end_time);
        return $result;
    }

    /**
     * 销售明细 -- 图表
     * @param array $start_end_time
     * @return array
     */
    public function getSalesStatisticsDetail(array $start_end_time): array
    {
        list($start_date, $end_date) = $start_end_time;
        // 横轴
        $horizontal_axis = app(StatisticsUserService::class)->getHorizontalAxis(0, $start_date, $end_date);
        // 支付金额
        $payment_amount_list = app(OrderService::class)->getPayMoneyList($start_end_time);
        $longitudinal_axis_payment_amount = app(StatisticsUserService::class)->getLongitudinalAxis($horizontal_axis, $payment_amount_list, 0, 4);

        // 退款金额
        $refund_amount_list = app(RefundApplyService::class)->getRefundList($start_end_time);
        $longitudinal_axis_refund_amount = app(StatisticsUserService::class)->getLongitudinalAxis($horizontal_axis, $refund_amount_list, 0, 6);

        // 商品浏览量
        $product_view_list = app(AccessLogService::class)->getVisitList($start_end_time, 1, 1);
        $longitudinal_axis_product_view = app(StatisticsUserService::class)->getLongitudinalAxis($horizontal_axis, $product_view_list, 0, 2);

        // 商品访客量
        $product_visitor_list = app(AccessLogService::class)->getVisitList($start_end_time, 0, 1);
        $longitudinal_axis_product_visitor = app(StatisticsUserService::class)->getLongitudinalAxis($horizontal_axis, $product_visitor_list, 0, 2);

        $result = [
            "horizontal_axis" => $horizontal_axis,
            "longitudinal_axis_payment_amount" => $longitudinal_axis_payment_amount,
            "longitudinal_axis_refund_amount" => $longitudinal_axis_refund_amount,
            "longitudinal_axis_product_view" => $longitudinal_axis_product_view,
            "longitudinal_axis_product_visitor" => $longitudinal_axis_product_visitor,
        ];
        return $result;
    }

    /**
     * 销售商品明细
     * @param array $filter
     * @return array
     */
    public function getSaleProductDetail(array $filter): array
    {
        $start_end_time = [];
        if (!empty($filter["start_time"]) && !empty($filter["end_time"])) {
            $start_end_time = [$filter["start_time"], $filter["end_time"]];
        }
        $query = OrderItem::hasWhere("orders", function ($query) use ($start_end_time) {
            $query->where("is_del", 0)->addTime($start_end_time)->validOrder()->storePlatform();
        })
            ->with("orders")
            ->visible(['orders' => ['order_sn', 'add_time']])
            ->keyword($filter["keyword"])
            ->field("(quantity * price) AS subtotal");
        $count = $query->count();
        $total_list = $query->select()->toArray();
        $list = $query->page($filter["page"], $filter["size"])->order($filter["sort_field"], $filter["sort_order"])->select()->toArray();
        $result = [
            "count" => $count,
            "list" => $list,
        ];

        if ($filter["is_export"]) {
            // 导出
            $data = [];
            foreach ($total_list as $item) {
                $data[] = [
                    "product_name" => $item["product_name"],
                    "product_sn" => $item["product_sn"],
                    "sku_data" => is_array($item["sku_data"]) ? implode(":", $item["sku_data"]) : "",
                    "order_sn" => $item["order"]["order_sn"],
                    "quantity" => $item["quantity"],
                    "price" => $item["price"],
                    "subtotal" => $item["subtotal"],
                    "add_time" => $item["order"]["add_time"],
                ];
            }
            app(StatisticsUserService::class)->executeExport($data, 0, 6);
        }
        return $result;
    }

    /**
     * 销售指标
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function getSaleIndicators(): array
    {
        //订单总数
        $order_num = Order::where("is_del", 0)->ValidOrder()->storePlatform()->count();
        //订单商品总数
        $order_product_num = OrderItem::hasWhere("orders", function ($query) {
            $query->where("is_del", 0)->validOrder()->paid()->storePlatform();
        })->count();
        //订单总金额
        $order_total_amount = Order::where("is_del", 0)->ValidOrder()->storePlatform()->sum('total_amount');
        //会员总数
        $user_num = User::count();
        //消费会员总数
        $consumer_membership_num = Order::where("is_del", 0)->ValidOrder()->storePlatform()->group('user_id')->count();
        //人均消费数
        $capita_consumption = number_format($order_total_amount / $user_num, 2, '.', '');
        //访问数 -- 商品点击数
        $click_count = Product::where("is_delete", 0)->storePlatform()->sum('click_count');
        //访问转化率
        $click_rate = number_format(($order_num / $click_count) * 100, 2, '.', '');
        //订单转化率
        $order_rate = number_format(($order_total_amount / $click_count) * 100, 2, '.', '');
        //消费会员比率
        $consumer_membership_rate = number_format(($consumer_membership_num / $user_num) * 100, 2, '.', '');
        //购买率
        $purchase_rate = number_format(($order_num / $user_num) * 100, 2, '.', '');
        $result = [
            "order_num" => $order_num,
            "order_product_num" => $order_product_num,
            "order_total_amount" => $order_total_amount,
            "user_num" => $user_num,
            "consumer_membership_num" => $consumer_membership_num,
            "capita_consumption" => $capita_consumption,
            "click_count" => $click_count,
            "click_rate" => $click_rate,
            "order_rate" => $order_rate,
            "consumer_membership_rate" => $consumer_membership_rate,
            "purchase_rate" => $purchase_rate,
        ];
        return $result;
    }

    /**
     * 销售排行
     * @param array $filter
     * @return array
     */
    public function getSalesRanking(array $filter): array
    {
        $start_end_time = [];
        if (!empty($filter["start_time"]) && !empty($filter["end_time"])) {
            $start_end_time = [$filter["start_time"], $filter["end_time"]];
        }
        $query = OrderItem::hasWhere("orders", function ($query) use ($start_end_time) {
            $query->where("is_del", 0)->addTime($start_end_time)->validOrder()->storePlatform();
        })
            ->keyword($filter["keyword"])
            ->field("SUM(quantity * price) AS total_sales_amount,SUM(quantity) AS total_sales_num")
            ->group("product_id");

        $count = $query->count();
        $total_list = $query->select()->toArray();
        $list = $query->page($filter["page"], $filter["size"])->order($filter["sort_field"], $filter["sort_order"])->select()->toArray();
        $result = [
            "count" => $count,
            "list" => $list,
        ];

        if ($filter["is_export"]) {
            // 导出
            $data = [];
            foreach ($total_list as $item) {
                $sku_data = "";
                if(!empty($item["sku_data"])){
                    // 平铺数组并以:分隔
                    $sku_data = array_map(function ($subArray) {
                        return implode(':', $subArray);
                    }, $item["sku_data"]);
                    $sku_data = implode('|', $sku_data);
                }
                $data[] = [
                    "product_name" => $item["product_name"],
                    "product_sn" => $item["product_sn"],
                    "sku_data" => $sku_data,
                    "total_sales_num" => $item["total_sales_num"],
                    "total_sales_amount" => $item["total_sales_amount"],
                ];
            }

            app(StatisticsUserService::class)->executeExport($data, 0, 7);
        }
        return $result;
    }
}
