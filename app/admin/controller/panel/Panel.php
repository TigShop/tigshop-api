<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 面板
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\admin\controller\panel;

use app\admin\AdminBaseController;
use app\service\api\admin\authority\AuthorityService;
use app\service\api\admin\panel\SalesStatisticsService;
use think\App;
use think\Response;

/**
 * 面板控制器
 */
class Panel extends AdminBaseController
{

    /**
     * 构造函数
     *
     * @param App $app
     */
    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->checkAuthor('panelManage'); //权限检查
    }

    /**
     * 首页面板-控制台
     *
     * @return Response
     */
    public function list(): Response
    {
        // 控制台数据
        $console_data = app(SalesStatisticsService::class)->getConsoleData();
        // 实时数据
        $real_time_data = app(SalesStatisticsService::class)->getRealTimeData();
        //统计图表
        $panel_statistical_data = app(SalesStatisticsService::class)->getPanelStatisticalData();
        return $this->success([
            'console_data' => $console_data,
            'real_time_data' => $real_time_data,
            'panel_statistical_data' => $panel_statistical_data,
        ]);
    }

    /**
     * 一键直达
     *
     * @return Response
     */
    public function searchMenu(): Response
    {
        $keyword = input('keyword', '');
        $keyword = trim($keyword);
        $item = app(AuthorityService::class)->getAuthorityList($keyword);
        return $this->success([
            'item' => $item,
        ]);
    }
}
