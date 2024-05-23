<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 访问统计
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\panel;

use app\model\sys\AccessLog;
use app\service\api\admin\BaseService;
use exceptions\ApiException;

class StatisticsAccessService extends BaseService
{
    /**
     * 访问统计
     * @param array $filter
     * @return array
     * @throws ApiException
     */
    public function getAccessStatistics(array $filter)
    {
        if (empty($filter["end_time"]) || empty($filter["start_time"])) {
            throw new ApiException('请选择日期');
        }
        $start_end_time = [$filter["start_time"], $filter["end_time"]];
        if ($filter["is_hits"]) {
            // 点击量统计
            $data = AccessLog::field("DATE_FORMAT(FROM_UNIXTIME(access_time), '%Y-%m-%d') AS period")
                ->field("COUNT(*) AS access_count")
                ->accessTime($start_end_time)
                ->storePlatform()
                ->group("period")
                ->select();
        } else {
            // 访客数统计
            $data = AccessLog::field("DATE_FORMAT(FROM_UNIXTIME(access_time), '%Y-%m-%d') AS period")
                ->field("COUNT(DISTINCT ip_address) AS access_count")
                ->accessTime($start_end_time)
                ->storePlatform()
                ->group("period")
                ->select();
        }

        $data = $data->toArray();

        // 横轴
        $horizontal_axis = app(StatisticsUserService::class)->getHorizontalAxis(0, $filter["start_time"], $filter["end_time"]);
        // 纵轴
        $longitudinal_axis = app(StatisticsUserService::class)->getLongitudinalAxis($horizontal_axis, $data, 0, 2);

        $result = [
            "horizontal_axis" => $horizontal_axis,
            "longitudinal_axis" => $longitudinal_axis,
        ];
        return $result;
    }
}
