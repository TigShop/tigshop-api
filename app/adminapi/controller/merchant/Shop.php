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
use app\service\api\admin\merchant\ShopService;
use app\validate\shop\ShopValidate;
use think\App;

/**
 * 店铺控制器
 */
class Shop extends AdminBaseController
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
     * 列表页面
     *
     * @return \think\Response
     */
    public function list(): \think\Response
    {
        $filter = $this->request->only([
            'shop_id' => 0,
            'page/d' => 1,
            'size/d' => 15,
            'sort_field' => 'shop_id',
            'sort_order' => 'desc',
        ], 'get');

        $filterResult = $this->shopService->getFilterList($filter, ['merchant']);
        $total = $this->shopService->getFilterCount($filter);

        return $this->success([
            'filter_result' => $filterResult,
            'filter' => $filter,
            'total' => $total,
        ]);
    }

    /**
     * 我的店铺 商户端专用
     * @return \think\Response
     */
    public function myShop(): \think\Response
    {
        $filter = $this->request->only([
            'keyword' => '',
            'shop_id' => 0,
            'page/d' => 1,
            'size/d' => 15,
            'merchant_id' => request()->merchantId,
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
     * 列表页面
     *
     * @return \think\Response
     */
    public function all(): \think\Response
    {
        $store = $this->shopService->getAllShop();
        return $this->success([
            'store' => $store,
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
        $item = $this->shopService->getDetail($id);
        return $this->success([
            'item' => $item,
        ]);
    }

    /**
     * 执行添加操作
     *
     * @return \think\Response
     */
    public function create(): \think\Response
    {
        $data = $this->request->only([
            'shop_title' => '',
            'shop_logo' => '',
        ], 'post');
        $data['merchant_id'] = request()->merchantId;
        $result = $this->shopService->updateShop(0, $data, true);
        if ($result) {
            return $this->success('店铺添加成功');
        } else {
            return $this->error('店铺更新失败');
        }
    }

    /**
     * 执行更新操作
     *
     * @return \think\Response
     */
    public function update(): \think\Response
    {
        $id = input('shop_id/d', 0);
        $data = $this->request->only([
            'shop_id' => $id,
            'shop_title' => '',
            'shop_logo' => '',
            'contact_mobile' => '',
            'description' => '',
            'status' => 1,
        ], 'post');

        $result = $this->shopService->updateShop($id, $data, false);
        if ($result) {
            return $this->success('店铺更新成功');
        } else {
            return $this->error('店铺更新失败');
        }
    }

    /**
     * 更新单个字段
     *
     * @return \think\Response
     */
    public function updateField(): \think\Response
    {
        $id = input('shop_id/d', 0);
        $field = input('field', '');

        if (!in_array($field, ['status'])) {
            return $this->error('#field 错误');
        }
        $val = input('val');
        $data = [
            $field => $val,
        ];
        if ($field == 'status' && !in_array($data['val'], array_keys(\app\model\merchant\Shop::STATUS_LIST))) {
            return $this->error('无效的状态值');
        }
        $this->shopService->updateShopField($id, $data);

        return $this->success('更新成功');
    }

    /**
     * 删除
     *
     * @return \think\Response
     */
    public function del(): \think\Response
    {
        $id = input('id/d', 0);
        $this->shopService->deleteShop($id);
        return $this->success('指定项目已删除');
    }

    /**
     * 批量操作
     *
     * @return \think\Response
     */
    public function batch(): \think\Response
    {
        if (empty(input('ids')) || !is_array(input('ids'))) {
            return $this->error('未选择项目');
        }

        if (input('type') == 'del') {
            foreach (input('ids') as $key => $id) {
                $id = intval($id);
                $this->shopService->deleteShop($id);
            }
            return $this->success('批量操作执行成功！');
        } else {
            return $this->error('#type 错误');
        }
    }
}
