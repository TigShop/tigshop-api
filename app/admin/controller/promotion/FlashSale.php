<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 秒杀
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\admin\controller\promotion;

use app\admin\AdminBaseController;
use app\service\api\admin\promotion\FlashSaleService;
use think\App;
use think\response\Json;

/**
 * 秒杀控制器
 */
class FlashSale extends AdminBaseController
{
    protected FlashSaleService $flashSaleService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param FlashSaleService $flashSaleService
     */
    public function __construct(App $app, FlashSaleService $flashSaleService)
    {
        parent::__construct($app);
        $this->flashSaleService = $flashSaleService;
        $this->checkAuthor('flashSaleManage'); //权限检查
    }

    /**
     * 列表页面
     *
     * @return Json
     */
    public function list(): Json
    {
        $filter = $this->request->only([
            'keyword' => '',
            'page/d' => 1,
            'size/d' => 15,
            'sort_field' => 'flash_sale_id',
            'sort_order' => 'desc',
        ], 'get');

        $filterResult = $this->flashSaleService->getFilterResult($filter);
        $total = $this->flashSaleService->getFilterCount($filter);

        return $this->success([
            'filter_result' => $filterResult,
            'filter' => $filter,
            'total' => $total,
        ]);
    }

    /**
     * 添加或编辑页面
     *
     * @return Json
     */
    public function edit(): Json
    {
        if ($this->isAdd) {
            $item = [
                'sort_order' => 50,
            ];
        } else {
            $id = input('id/d', 0);
            $item = $this->flashSaleService->getDetail($id);
        }

        return $this->success([
            'item' => $item,
        ]);
    }

    /**
     * 执行添加或更新操作
     *
     * @return Json
     */
    public function update(): Json
    {
        $id = input('id/d', 0);
        $data = $this->request->only([
            'flash_sale_id' => $id,
            'flash_sale_name' => '',
            'start_hour' => '',
            'total_hour' => '',
            'is_enabled' => '',
            'product_ids' => '',
            'start_date' => '',
        ], 'post');

        $result = $this->flashSaleService->updateFlashSale($id, $data, $this->isAdd);
        if ($result) {
            return $this->success($this->isAdd ? '秒杀添加成功' : '秒杀更新成功');
        } else {
            return $this->error('秒杀更新失败');
        }
    }

    /**
     * 更新单个字段
     *
     * @return Json
     */
    public function update_field(): Json
    {
        $id = input('id/d', 0);
        $field = input('field', '');

        if (!in_array($field, ['flash_sale_name', 'sort_order', 'is_enabled'])) {
            return $this->error('#field 错误');
        }

        $data = [
            'flash_sale_id' => $id,
            $field => input('val'),
        ];

        $this->flashSaleService->updateFlashSaleField($id, $data);

        return $this->success('更新成功');
    }

    /**
     * 删除
     *
     * @return Json
     */
    public function del(): Json
    {
        $id = input('id/d', 0);
        $this->flashSaleService->deleteFlashSale($id);
        return $this->success('指定项目已删除');
    }

    /**
     * 批量操作
     *
     * @return Json
     */
    public function batch(): Json
    {
        if (empty(input('ids')) || !is_array(input('ids'))) {
            return $this->error('未选择项目');
        }

        if (input('type') == 'del') {
            foreach (input('ids') as $key => $id) {
                $id = intval($id);
                $this->flashSaleService->deleteFlashSale($id);
            }
            return $this->success('批量操作执行成功！');
        } else {
            return $this->error('#type 错误');
        }
    }
}
