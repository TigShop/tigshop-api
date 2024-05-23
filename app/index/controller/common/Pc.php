<?php
//**---------------------------------------------------------------------+
//** 通用接口控制器文件 -- 通用
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\index\controller\common;

use app\index\IndexBaseController;
use app\service\api\admin\decorate\PcCatFloorService;
use app\service\api\admin\decorate\PcNavigationService;
use think\App;
use think\Response;
use utils\Config;

/**
 * 首页控制器
 */
class Pc extends IndexBaseController
{
    /**
     * 构造函数
     *
     * @param App $app
     */
    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    /**
     * 首页
     *
     * @return Response
     */
    public function getHeader(): Response
    {
        return $this->success([
        ]);
    }

    /**
     * 获取PC导航栏
     * @return Response
     */
    public function getNav(): Response
    {
        $nav = app(PcNavigationService::class)->getAllNav();
        return $this->success($nav);
    }

    /**
     * 获取PC分类抽屉
     * @return Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getCatFloor(): Response
    {
        $nav = app(PcCatFloorService::class)->getCatFloor();
        return $this->success([
            'cat_floor' => $nav,
            'ico_defined_css' => Config::get('ico_defined_css'),
        ]);
    }

}
