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
use app\service\api\admin\merchant\ShopWithdrawService;
use think\App;

/**
 * 店铺提现控制器
 */
class ShopWithdraw extends AdminBaseController
{
    protected ShopWithdrawService $shopWithdrawService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param ShopWithdrawService $shopWithdrawService
     */
    public function __construct(App $app, ShopWithdrawService $shopWithdrawService)
    {
        parent::__construct($app);
        $this->shopWithdrawService = $shopWithdrawService;
    }

    /**
     * 列表页面
     *
     * @return \think\Response
     */
    public function list(): \think\Response
    {
        $filter = $this->request->only([
            'shop_id' => $this->shopId,
            'page/d' => 1,
            'size/d' => 15,
            'sort_field' => 'shop_id',
            'sort_order' => 'desc',
            'status' => -1
        ], 'get');

        $filterResult = $this->shopWithdrawService->getFilterList($filter);
        $total = $this->shopWithdrawService->getFilterCount($filter);

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
        $item = $this->shopWithdrawService->getDetail($id);
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
            'amount' => '',
            'merchant_account_id' => '',
            'account_data' => ''
        ], 'post');
        $data['shop_id'] = request()->shopId;
        $result = $this->shopWithdrawService->create($data);
        if ($result) {
            return $this->success('店铺添加成功');
        } else {
            return $this->error('店铺更新失败');
        }
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
                $this->shopWithdrawService->deleteShop($id);
            }
            return $this->success('批量操作执行成功！');
        } else {
            return $this->error('#type 错误');
        }
    }
}
