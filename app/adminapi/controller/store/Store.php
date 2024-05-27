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

namespace app\adminapi\controller\store;

use app\adminapi\AdminBaseController;
use app\service\api\admin\store\StoreService;
use think\App;

/**
 * 店铺控制器
 */
class Store extends AdminBaseController
{
    protected StoreService $storeService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param StoreService $storeService
     */
    public function __construct(App $app, StoreService $storeService)
    {
        parent::__construct($app);
        $this->storeService = $storeService;
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
            'store_id' => 0,
            'is_self' => -1,
            'page/d' => 1,
            'size/d' => 15,
            'sort_field' => 'store_id',
            'sort_order' => 'desc',
        ], 'get');

        $filterResult = $this->storeService->getFilterResult($filter);
        $total = $this->storeService->getFilterCount($filter);

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
        $store = $this->storeService->getAllStore();
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

        $id = input('id/d', 0);
        $item = $this->storeService->getDetail($id);
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
            'store_title' => '',
            'sort_order/d' => 50,
        ], 'post');

        $result = $this->storeService->updateStore(0, $data, true);
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
        $id = input('id/d', 0);
        $data = $this->request->only([
            'store_id' => $id,
            'store_title' => '',
            'sort_order/d' => 50,
        ], 'post');

        $result = $this->storeService->updateStore($id, $data, false);
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
        $id = input('id/d', 0);
        $field = input('field', '');

        if (!in_array($field, ['store_title', 'sort_order'])) {
            return $this->error('#field 错误');
        }

        $data = [
            'store_id' => $id,
            $field => input('val'),
        ];

        $this->storeService->updateStoreField($id, $data);

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
        $this->storeService->deleteStore($id);
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
                $this->storeService->deleteStore($id);
            }
            return $this->success('批量操作执行成功！');
        } else {
            return $this->error('#type 错误');
        }
    }
}
