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

namespace app\api\controller\shop;

use app\api\IndexBaseController;
use app\model\user\CollectShop;
use app\service\api\admin\decorate\DecorateDiscreteService;
use app\service\api\admin\decorate\DecorateService;
use app\service\api\admin\decorate\MobileCatNavService;
use app\service\api\admin\merchant\ShopService;
use app\service\api\admin\product\ProductService;
use app\service\api\admin\promotion\CouponService;
use app\service\api\admin\promotion\SeckillService;
use app\service\api\admin\setting\FriendLinksService;
use app\service\api\index\user\CollectShopService;
use think\App;
use think\Response;
use utils\Config;

/**
 * 店铺控制器
 */
class Shop extends IndexBaseController
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
     * 装修
     *
     * @return Response
     */
    public function decorate(): Response
    {
        // 获取首页发布版
        $shopId = input('shop_id/d', 0);
        $decorate = app(DecorateService::class)->getShopDecorateModule($shopId);
        return $this->success([
            'decorate_id' => $decorate['decorate_id'],
            'module_list' => $decorate['module_list'],
        ]);
    }

    /**
     * 详情
     *
     * @return \think\Response
     */
    public function detail(): \think\Response
    {
        $id = input('shop_id/d', 0);
        $item = app(ShopService::class)->getDetail($id);
        $item['product_count'] = app(ProductService::class)->getFilterCount([
            'shop_id' => $id,
        ]);
        $item['new_product_count'] = app(ProductService::class)->getFilterCount([
            'shop_id' => $id,
            'is_new' => 1,
        ]);
        $item['collect_shop'] = false;
        if (request()->userId > 0) {
            $item['collect_shop'] = (bool)app(CollectShopService::class)->getDetail([
                'shop_id' => $id,
                'user_id' => request()->userId,
            ]);
        }
        return $this->success([
            'item' => $item,
        ]);
    }

    /**
     * 收藏
     * @return Response
     */
    public function collect(): \think\Response
    {
        $id = input('shop_id/d', 0);
        $userId = request()->userId;
        $service = app(CollectShopService::class);
        $item = $service->getDetail([
            'shop_id' => $id,
            'user_id' => $userId,
        ]);
        if ($item) {
            $service->delete($item->getKey());
        } else {
            $service->create([
                'shop_id' => $id,
                'user_id' => $userId,
            ]);
        }
        return $this->success();
    }


}
