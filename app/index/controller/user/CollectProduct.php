<?php
//**---------------------------------------------------------------------+
//** 通用接口控制器文件 -- 收藏商品                                 +
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\index\controller\user;

use app\index\IndexBaseController;
use app\service\api\admin\user\CollectProductService;
use think\App;
use think\Response;

/**
 * 收藏的商品控制器
 */
class CollectProduct extends IndexBaseController
{
    protected CollectProductService $collectProductService;
    /**
     * 构造函数
     *
     * @param App $app
     * @param CollectProductService $collectProductService
     */
    public function __construct(App $app, CollectProductService $collectProductService)
    {
        parent::__construct($app);
        $this->checkLogin();
        $this->collectProductService = $collectProductService;
    }

    /**
     * 商品收藏列表
     * @return Response
     */
    public function list(): Response
    {
        $filter = $this->request->only([
            'keyword' => '',
            'page' => 1,
            'size' => 15,
            'sort_field' => 'collect_id',
            'sort_order' => 'desc',
        ], 'get');
        $filterResult = $this->collectProductService->getFilterResult($filter);
        return $this->success([
            'filter_result' => $filterResult["list"],
            'total' => $filterResult["count"],
            'filter' => $filter,
        ]);
    }

    /**
     * 收藏商品
     * @return Response
     */
    public function save(): Response
    {
        $product_id = input("product_id/d", 0);
        $result = $this->collectProductService->updateCollect($product_id, request()->userId);
        return $result ? $this->success(/** LANG */'收藏成功') : $this->error(/** LANG */'收藏失败');
    }

    /**
     * 取消收藏
     * @return Response
     * @throws \app\common\exceptions\ApiException
     */
    public function cancel(): Response
    {
        $id = input("id/d", 0);
        $result = $this->collectProductService->deleteCollect($id);
        return $result ? $this->success(/** LANG */'取消收藏成功') : $this->error(/** LANG */'取消收藏失败');
    }
}
