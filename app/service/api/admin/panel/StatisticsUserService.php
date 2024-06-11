<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 新增会员统计面板
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\panel;

use app\model\order\Order;
use app\model\user\User;
use app\service\api\admin\finance\UserRechargeOrderService;
use app\service\api\admin\order\OrderService;
use app\service\api\admin\sys\AccessLogService;
use app\service\api\admin\user\UserService;
use app\service\core\BaseService;
use exceptions\ApiException;
use utils\Excel;
use utils\Time;

class StatisticsUserService extends BaseService
{
    /**
     * 新增会员趋势统计图
     * @param array $filter
     * @return array
     * @throws ApiException
     */
    public function getAddUserTrends(array $filter): array
    {
        if (empty($filter["start_end_time"])) {
            throw new ApiException('请选择日期');
        }
        $start_end_time = $this->getDateRange($filter["date_type"], $filter["start_end_time"]);

        list($start_date, $end_date) = $start_end_time;
        $list = app(UserService::class)->filterQuery([
                "reg_time" => $start_end_time
            ])
            ->field("user_id,reg_time")
            ->select()->toArray();

        // 横轴
        $horizontal_axis = $this->getHorizontalAxis($filter["date_type"], $start_date, $end_date);
        // 纵轴
        $longitudinal_axis = $this->getLongitudinalAxis($horizontal_axis, $list, $filter["date_type"], 1);

        $data = [
            "horizontal_axis" => $horizontal_axis,
            "longitudinal_axis" => $longitudinal_axis,
        ];
        if ($filter["is_export"]) {
            // 导出
            $this->executeExport($data, $filter["date_type"], 1);
        }
        // 统计图
        return [
            "horizontal_axis" => $horizontal_axis,
            "longitudinal_axis" => $longitudinal_axis,
        ];
    }

    /**
     * 会员消费排行
     * @param array $filter
     * @return array|bool
     */
    public function getUserConsumptionRanking(array $filter): array|bool
    {
        $list = $this->getFilterData($filter);
        if ($filter["is_export"]) {
            // 导出
            $list = $list->select()->toArray();
            $this->executeExport($list, 0, 2);
            return true;
        } else {
            // 列表
            $list = $list->page($filter['page'], $filter['size'])->select()->toArray();
            return $list;
        }
    }

    /**
     * 获取筛选结果数量
     * @param array $filter
     * @return int
     */
    public function getFilterCount(array $filter): int
    {
        $query = $this->getFilterData($filter);
        $count = $query->count();
        return $count;
    }

    /**
     * 筛选数据
     * @param array $filter
     * @return mixed
     */
    public function getFilterData(array $filter): mixed
    {
        $start_end_time = [];
        if (!empty($filter["start_time"]) && !empty($filter["end_time"])) {
            $start_end_time = [$filter["start_time"], $filter["end_time"]];
        }

        $query = Order::leftJoin("user", "user.user_id = order.user_id")
            ->where("order.pay_status", ">", 0)
            ->where("order.is_del", 0)
            ->addTime($start_end_time)
            ->storePlatform()
            ->field("user.username,user.mobile,COUNT(order.order_id) as order_num,user.order_amount")
            ->group("order.user_id");

        if ($filter["sort_field"] == "order_amount") {
            $query = $query->order("user.order_amount", $filter["sort_order"]);
        } elseif ($filter["sort_field"] == "order_num") {
            $query = $query->order("order_num", $filter["sort_order"]);
        }

        if (!empty($filter["keyword"])) {
            $query->where(function ($query) use ($filter) {
                $query->where('user.username', 'like', '%' . $filter['keyword'] . '%')
                    ->whereOr('user.mobile', 'like', '%' . $filter['keyword'] . '%');
            });
        }
        return $query;
    }

    /**
     * 用户统计面板
     * @param array $filter
     * @return array
     */
    public function getUserStatisticsPanel(array $filter): array
    {
        $start_end_time = [];
        if (!empty($filter["start_time"]) && !empty($filter["end_time"])) {
            $start_end_time = [$filter["start_time"], $filter["end_time"]];
        }
        // 获取环比时间区间
        $prev_date = $this->getPrevDate($start_end_time);

        // 访客数
        // 本期访问量
        $visit_num = app(AccessLogService::class)->getVisitNum($start_end_time);
        // 上期访问量
        $prev_visit_num = app(AccessLogService::class)->getVisitNum($prev_date);
        // 环比增长率
        $visit_growth_rate = $this->getGrowthRate($visit_num, $prev_visit_num);

        // 浏览量
        $view_num = app(AccessLogService::class)->getVisitNum($start_end_time, 1);
        $prev_view_num = app(AccessLogService::class)->getVisitNum($prev_date, 1);
        $view_growth_rate = $this->getGrowthRate($view_num, $prev_view_num);

        // 新增用户数
        $add_user_num = User::RegTime($start_end_time)->count();
        $prev_add_user_num = User::RegTime($prev_date)->count();
        $add_user_growth_rate = $this->getGrowthRate($add_user_num, $prev_add_user_num);

        //成交用户数
        $deal_user_num = app(OrderService::class)->getPayOrderUserTotal($start_end_time);
        $prev_deal_user_num = app(OrderService::class)->getPayOrderUserTotal($prev_date);
        $deal_user_growth_rate = $this->getGrowthRate($deal_user_num, $prev_deal_user_num);

        // 充值用户数
        $recharge_user_num = app(UserRechargeOrderService::class)->getRechargeUserTotal($start_end_time);
        $prev_recharge_user_num = app(UserRechargeOrderService::class)->getRechargeUserTotal($prev_date);
        $recharge_user_growth_rate = $this->getGrowthRate($recharge_user_num, $prev_recharge_user_num);

        //访客 - 支付转化率
        if (!empty($visit_num) && !empty($add_user_num)) {
            $visit_to_user = number_format(($add_user_num / $visit_num) * 100, 2, '.', '');
        } else {
            $visit_to_user = 0;
        }
        if (!empty($prev_add_user_num) && !empty($prev_visit_num)) {
            $prev_visit_to_user = number_format(($prev_add_user_num / $prev_visit_num) * 100, 2, '.', '');
        } else {
            $prev_visit_to_user = 0;
        }
        $visit_to_user_rate = $this->getGrowthRate($visit_to_user, $prev_visit_to_user);
        $result = [
            "visit_num" => $visit_num,
            "visit_growth_rate" => $visit_growth_rate,
            "view_num" => $view_num,
            "view_growth_rate" => $view_growth_rate,
            "add_user_num" => $add_user_num,
            "add_user_growth_rate" => $add_user_growth_rate,
            "deal_user_num" => $deal_user_num,
            "deal_user_growth_rate" => $deal_user_growth_rate,
            "visit_to_user" => $visit_to_user,
            "visit_to_user_rate" => $visit_to_user_rate,
            "recharge_user_num" => $recharge_user_num,
            "recharge_user_growth_rate" => $recharge_user_growth_rate,
        ];
        if ($filter["is_export"]) {
            // 导出
            $this->executeExport($result, 0, 3);
        }
        // 数据
        return $result;
    }

    /**
     * 获取环比时间区间
     * @param array $start_end_time
     * @return array
     */
    public function getPrevDate(array $start_end_time): array
    {
        if (!empty($start_end_time)) {
            list($start_date, $end_date) = $start_end_time;
            $start_date = Time::toTime($start_date);
            $end_date = Time::toTime($end_date);
            $prev_date = 2 * $start_date - $end_date;
            return [Time::format($prev_date, "Y-m-d"), Time::format($start_date, "Y-m-d")];
        }
        return [];
    }

    /**
     * 环比增长率
     * @param int|float $today_data
     * @param int|float $prev_data
     * @return float|string
     */
    public function getGrowthRate(int | float $today_data, int | float $prev_data): float|string
    {
        if (empty($today_data) || empty($prev_data)) {
            return "--";
        }
        $rate = number_format(100 * ($today_data - $prev_data) / $prev_data, 2, '.', '');
        return $rate;
    }

    /**
     * 设置导出数据
     * @param array $horizontal_axis
     * @param array $longitudinal_axis
     * @param int $date_type
     * @return array
     */
    public function setExportData(array $horizontal_axis, array $longitudinal_axis, int $date_type): array
    {
        $export_data = [];
        foreach ($horizontal_axis as $k => $v) {
            switch ($date_type) {
                case 1:
                    // 年
                    $export_data[] = [
                        $v . "月",
                        $longitudinal_axis[$k] ?? 0,
                    ];
                    break;
                case 2:
                    // 月
                    $export_data[] = [
                        $v . "日",
                        $longitudinal_axis[$k] ?? 0,
                    ];
                    break;
                case 3:
                    // 日
                    $export_data[] = [
                        $v . "h",
                        $longitudinal_axis[$k] ?? 0,
                    ];
                    break;
                default:
                    // 自定义
                    $export_data[] = [
                        $v,
                        $longitudinal_axis[$k] ?? 0,
                    ];
                    break;
            }
        }
        return $export_data;
    }

    /**
     * 获取时间区间
     * @param int $date_type
     * @param string|array $start_end_time
     * @return array
     */
    public function getDateRange(int $date_type, string | array $start_end_time): array
    {
        switch ($date_type) {
            case 1:
                // 年  -- 显示月份
                $start_date = $start_end_time . '-01-01';
                $end_date = $start_end_time . '-12-31';
                break;
            case 2:
                //月 -- 显示日期
                $start_date = $start_end_time . "-01";
                $end_date = date("Y-m-t", Time::toTime($start_date));
                break;
            case 3:
                // 日 -- 显示24小时
                $start_date = $end_date = $start_end_time;
                break;
            default:
                // 自定义 -- 数组类型
                list($start_date, $end_date) = $start_end_time;
                $start_date = trim($start_date);
                $end_date = trim($end_date);
        }
        return [$start_date, $end_date];
    }

    /**
     * 获取横轴数据
     * @param int $date_type
     * @param string $start_date
     * @param string $end_date
     * @return array|string[]
     */
    public function getHorizontalAxis(int $date_type, string $start_date, string $end_date): array
    {
        $horizontal_axis = [];
        switch ($date_type) {
            case 1:
                // 年  -- 显示月份
                $horizontal_axis = ["01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12"];
                break;
            case 2:
                // 月 -- 显示日期
                $day = date("t", Time::toTime($start_date));
                for ($i = 1; $i <= $day; $i++) {
                    $horizontal_axis[] = $i;
                }
                break;
            case 3:
                // 日 -- 显示24小时
                for ($i = 0; $i < 24; $i++) {
                    $horizontal_axis[] = $i;
                }
                break;
            default:
                // 自定义 -- 时间数组
                $current_date = $start_date;
                while ($current_date <= $end_date) {
                    $horizontal_axis[] = $current_date;
                    $current_date = date('Y-m-d', strtotime($current_date . ' +1 day'));
                }
                break;
        }
        return $horizontal_axis;
    }

    /**
     * 获取纵轴数据
     * @param array $horizontal_axis
     * @param array $data
     * @param int $date_type
     * @param int $from_type
     * @return array
     */
    public function getLongitudinalAxis(array $horizontal_axis, array $data, int $date_type, int $from_type = 0): array
    {
        $longitudinal_axis = $range_data = [];
        foreach ($horizontal_axis as $key => $value) {
            if (in_array($date_type, [1, 2, 3])) {
                $range = str_pad($key + 1, 2, '0', STR_PAD_LEFT);
            } else {
                $range = $value;
            }
            $range_data[$range] = 0;
        }

        foreach ($data as $item) {
            switch ($from_type) {
                case 1:
                    // 新增会员统计
                    $reg_time = $item['reg_time'];
                    break;
                case 2: // 访问统计/访客统计
                case 3: // 订单统计
                    $reg_time = $item["period"];
                    break;
                case 4:
                    // 订单金额统计
                    $reg_time = $item["pay_time"];
                    $total_amount = $item["total_amount"];
                    break;
                case 5:
                    // 订单数统计
                    $reg_time = $item["pay_time"];
                    break;
                case 6:
                    // 退款金额统计
                    $reg_time = $item["add_time"];
                    $refund_amount = $item["refund_amount"];
                    break;
                case 7:
                    // 面板 -- 订单金额
                    $reg_time = $item["period"];
                    $order_amount = $item["order_amount"];
                    break;
            }
            switch ($date_type) {
                case 1:
                    // 年
                    $range = date('m', strtotime($reg_time));
                    break;
                case 2:
                    // 月
                    $range = date('d', strtotime($reg_time));
                    break;
                case 3:
                    // 日
                    $range = date('H', strtotime($reg_time));
                    break;
                default:
                    // 自定义
                    $range = date('Y-m-d', strtotime($reg_time));
                    break;
            }

            switch ($from_type) {
                case 1:
                    // 新增会员统计
                    $range_data[$range]++;
                    break;
                case 2:
                    // 访问统计
                    $range_data[$range] = $item['access_count'] ?? 0;
                    break;
                case 3:
                    // 订单统计
                    $range_data[$range] = $item['order_count'] ?? 0;
                    break;
                case 4:
                    // 订单金额统计
                    $range_data[$range] = bcadd($total_amount, $range_data[$range], 2);
                    break;
                case 5:
                    // 订单数统计
                    $range_data[$range]++;
                    break;
                case 6:
                    // 退款金额统计
                    $range_data[$range] = bcadd($refund_amount, $range_data[$range], 2);
                    break;
                case 7:
                    $range_data[$range] = bcadd($order_amount, $range_data[$range], 2);
                    break;
            }

        }

        $longitudinal_axis = array_values($range_data);
        return $longitudinal_axis;
    }

    /**
     * 执行导出
     * @param array $data
     * @param int $date_type
     * @param int $flag
     * @return void
     */
    public function executeExport(array $data, int $date_type = 0, int $flag = 0): void
    {
        switch ($flag) {
            case 1:
                // 新增会员趋势
                // 文件名
                $file_name = "新增会员趋势" . Time::getCurrentDatetime("Ymd") . rand(1000, 9999);
                // 文件标题
                $fields = ["时间", "新增人数"];
                // 数据
                $export_data = $this->setExportData($data["horizontal_axis"], $data["longitudinal_axis"], $date_type);
                break;
            case 2:
                // 会员消费排行
                $file_name = "会员消费排行" . Time::getCurrentDatetime("Ymd") . rand(1000, 9999);
                $fields = ["会员名称", "手机号", "订单数", "消费总额"];
                $export_data = $data;
                break;
            case 3:
                // 用户统计
                $file_name = "用户统计" . Time::getCurrentDatetime("Ymd") . rand(1000, 9999);
                $fields = ["访客数", "环比增长", "浏览量", "环比增长", "新增用户数", "环比增长", "成交用户数", "环比增长", "访客-支付转化率", "环比增长", "充值用户数", "环比增长"];
                // 将数组平铺
                $export_data = [array_values($data)];
                break;
            case 4:
                // 订单金额
                $file_name = "订单金额统计" . Time::getCurrentDatetime("Ymd") . rand(1000, 9999);
                $fields = ["时间", "订单金额"];
                $export_data = $this->setExportData($data["horizontal_axis"], $data["longitudinal_axis"], $date_type);
                break;
            case 5:
                // 订单数
                $file_name = "订单数统计" . Time::getCurrentDatetime("Ymd") . rand(1000, 9999);
                $fields = ["时间", "订单数"];
                $export_data = $this->setExportData($data["horizontal_axis"], $data["longitudinal_axis"], $date_type);
                break;
            case 6:
                // 销售商品明细
                $file_name = "销售商品明细导出" . Time::getCurrentDatetime("Ymd") . rand(1000, 9999);
                $fields = ['商品名称', '商品编号', '商品属性', '订单号', '购买数量', '单价', '小计', '下单时间'];
                $export_data = $data;
                break;
            case 7:
                // 销售排行
                $file_name = "销售排行导出" . Time::getCurrentDatetime("Ymd") . rand(1000, 9999);
                $fields = ['商品名称', '商品编号', '商品属性', '总销量', '总销售额'];
                $export_data = $data;
                break;
            default:
                break;
        }
        Excel::export($fields, $file_name, $export_data);
    }

}
