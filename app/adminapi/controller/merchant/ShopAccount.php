<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 店铺
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\adminapi\controller\merchant;

use app\adminapi\AdminBaseController;
use app\model\merchant\MerchantAccount;
use app\service\api\admin\merchant\MerchantAccountService;
use app\service\api\admin\merchant\MerchantService;
use app\service\api\admin\merchant\ShopAccountLogService;
use app\service\api\admin\merchant\ShopService;
use app\service\api\admin\order\OrderService;
use think\App;

/**
 * 店铺控制器
 */
class ShopAccount extends AdminBaseController
{
    protected ShopService $shopService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param ShopService $shopService
     */
    public function __construct(App $app, ShopService $shopService)
    {
        parent::__construct($app);
        $this->shopService = $shopService;
    }

    /**
     * 资产总览
     * @return \think\Response
     */
    public function index(): \think\Response
    {
        $shop_money = $this->shopService->getFilterSum(['shop_id' => $this->shopId], 'shop_money');
        $frozen_money = $this->shopService->getFilterSum(['shop_id' => $this->shopId], 'frozen_money');
        $un_settlement_money = app(OrderService::class)->getFilterSum([
            'shop_id' => $this->shopId,
            'is_settlement' => 0
        ], 'paid_amount');
        $card_count = app(MerchantAccountService::class)->getFilterCount(['merchant_id' => request()->merchantId]);
        $merchant = app(MerchantService::class)->getDetail(request()->merchantId);
        return $this->success([
            'item' => [
                'shop_money' => $shop_money,
                'frozen_money' => $frozen_money,
                'un_settlement_money' => $un_settlement_money,
                'card_count' => $card_count,
                'merchant' => $merchant
            ]
        ]);
    }

    /**
     * 列表页面
     *
     * @return \think\Response
     */
    public function list(): \think\Response
    {
        $filter = $this->request->only([
            'keyword' => '',
            'shop_id' => 0,
            'page/d' => 1,
            'size/d' => 15,
            'sort_field' => 'shop_id',
            'sort_order' => 'desc',
        ], 'get');

        $filterResult = $this->shopService->getFilterList($filter);
        $total = $this->shopService->getFilterCount($filter);

        return $this->success([
            'filter_result' => $filterResult,
            'filter' => $filter,
            'total' => $total,
        ]);
    }

    /**
     * 资金明细
     * @return \think\Response
     */
    public function logList(): \think\Response
    {
        $filter = $this->request->only([
            'page' => 1,
            'size' => 15,
            'shop_id' => $this->shopId,
        ]);

        $shopAccountLogService = app(ShopAccountLogService::class);
        $filterResult = $shopAccountLogService->getFilterList($filter);
        $total = $shopAccountLogService->getFilterCount($filter);
        return $this->success([
            'filter_result' => $filterResult,
            'filter' => $filter,
            'total' => $total,
        ]);
    }

    /**
     * 详情
     *
     * @return \think\Response
     */
    public function detail(): \think\Response
    {

        $id = input('id/d', 0);
        $item = $this->shopService->getDetail($id);
        return $this->success([
            'item' => $item,
        ]);
    }



    /**
     * 执行更新操作
     *
     * @return \think\Response
     */
    public function update(): \think\Response
    {
        $id = input('id/d', 0);
        $data = $this->request->only([
            'store_id' => $id,
            'shop_title' => '',
            'sort_order/d' => 50,
        ], 'post');

        $result = $this->shopService->updateShop($id, $data, false);
        if ($result) {
            return $this->success('店铺更新成功');
        } else {
            return $this->error('店铺更新失败');
        }
    }


}
