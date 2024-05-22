<?php
//**---------------------------------------------------------------------+
//** 通用接口控制器文件 -- 首页
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\index\controller\home;

use app\common\utils\Config;
use app\index\IndexBaseController;
use app\service\api\admin\decorate\DecorateDiscreteService;
use app\service\api\admin\decorate\DecorateService;
use app\service\api\admin\decorate\MobileCatNavService;
use app\service\api\admin\promotion\CouponService;
use app\service\api\admin\promotion\SeckillService;
use app\service\api\admin\setting\FriendLinksService;
use think\App;
use think\Response;

/**
 * 首页控制器
 */
class Home extends IndexBaseController
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
    public function index(): Response
    {
        $preview_id = input('preview_id/d', 0);
        if ($preview_id > 0) {
            // 预览
            $decorate = app(DecorateService::class)->getAppPreviewDecorate($preview_id);
        } else {
            // 获取首页发布版
            $decorate = app(DecorateService::class)->getAppHomeDecorate();
        }
        return $this->success([
            'decorate_id' => $decorate['decorate_id'],
            'module_list' => $decorate['module_list'],
        ]);
    }
    /**
     * PC首页
     *
     * @return Response
     */
    public function pcIndex(): Response
    {
        $preview_id = input('preview_id/d', 0);
        if ($preview_id > 0) {
            // 预览
            $decorate = app(DecorateService::class)->getPcPreviewDecorate($preview_id);
        } else {
            // 获取首页发布版
            $decorate = app(DecorateService::class)->getPcHomeDecorate();
        }
        return $this->success([
            'decorate_id' => $decorate['decorate_id'],
            'module_list' => $decorate['module_list'],
        ]);
    }

    /**
     * 首页今日推荐
     * @return Response
     * @throws \app\common\exceptions\ApiException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getRecommend(): Response
    {
        $decorate_id = input('decorate_id/d', 0);
        $module_index = input('module_index');
        $page = input('page/d', 1);

        $module = app(DecorateService::class)->getDecorateModuleData($decorate_id, $module_index, ['page' => $page, 'size' => 10]);
        return $this->success([
            'item' => $module,
        ]);
    }

    /**
     * 首页秒杀
     * @return Response
     */
    public function getSeckill(): Response
    {

        $data = [
            'size' => 15,
            'page' => input('page/d', 1),
            'un_started' => input('un_started/d', 0),
        ];

        $filterResult = app(SeckillService::class)->getSeckillProductList($data);
        return $this->success([
            'seckill_list' => $filterResult['list'],
            'total' => $filterResult['total'],
        ]);
    }

    /**
     * 首页优惠券
     * @return Response
     */
    public function getCoupon(): Response
    {
        $data = [
            'size' => 5,
            'valid_date' => 1,
            'is_show' => 1,
        ];

        $filterResult = app(CouponService::class)->getFilterResult($data);
        return $this->success([
            'coupon_list' => $filterResult,
        ]);
    }

    /**
     * 首页分类栏
     * @return Response
     */
    public function mobileCatNav(): Response
    {
        $data = [
            'is_show' => 1,
            'sort_field' => 'mobile_cat_nav_id',
            'sort_order' => 'desc',
        ];

        $filterResult = app(MobileCatNavService::class)->getFilterResult($data);

        return $this->success([
            'filter_result' => $filterResult,
        ]);
    }

    /**
     * 移动端导航栏
     * @return Response
     */
    public function mobileNav(): Response
    {
        $item = app(DecorateDiscreteService::class)->getDetail("mobile_nav");
        return $this->success([
            'item' => $item,
        ]);
    }

    /**
     * 客服
     * @return Response
     */
    public function kefu(): Response
    {
        $data = [];
        if (Config::get('kefu_type') == 0) {
            $show = false;
        } else {
            $show = true;
        }
        if (Config::get('kefu_type') == 1) {
            $data['url'] = config('app.kf.yzf_url') . Config::get('kefu_yzf_sign');
            $data['open_type'] = Config::get('kefu_yzf_type');
        } elseif (Config::get('kefu_type') == 2) {
            $data['url'] = config('app.kf.work_url') . Config::get('kefu_workwx_id');
            $data['open_type'] = 0;
        } elseif (Config::get('kefu_type') == 3) {
            $data['url'] = Config::get('kefu_code');
            $data['open_type'] = Config::get('kefu_code_blank');
        }
        $data['show'] = $show;
        return $this->success($data);
    }

    /**
     * pc端友情链接接口
     * @return Response
     */
    public function friendLinks(): Response
    {
        $list = app(FriendLinksService::class)->getFilterResult([
            'sort_field' => 'sort_order',
            'sort_order' => 'desc',
            'page' => 1,
            'size' => 20,
        ]);
        return $this->success([
            'list' => $list,
        ]);
    }

}
