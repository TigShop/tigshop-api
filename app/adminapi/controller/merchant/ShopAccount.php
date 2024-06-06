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
use app\service\api\admin\merchant\ShopAccountLogService;
use app\service\api\admin\merchant\ShopService;
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
        $this->checkAuthor('storeManage'); //权限检查
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

        $filterResult = $this->shopService->getFilterResult($filter);
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
            'shop_id' => 0,
        ]);
        $shopAccountLogService = app(ShopAccountLogService::class);
        $filterResult = $shopAccountLogService->getFilterResult($filter);
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
