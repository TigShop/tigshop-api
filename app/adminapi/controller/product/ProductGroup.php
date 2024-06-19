<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 商品分组
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\adminapi\controller\product;

use app\adminapi\AdminBaseController;
use app\service\api\admin\product\ProductGroupService;
use app\validate\product\BrandValidate;
use think\App;
use think\exception\ValidateException;

/**
 * 商品分组控制器
 */
class ProductGroup extends AdminBaseController
{
    protected ProductGroupService $productGroupService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param ProductGroupService $productGroupService
     */
    public function __construct(App $app, ProductGroupService $productGroupService)
    {
        parent::__construct($app);
        $this->productGroupService = $productGroupService;
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
            'page/d' => 1,
            'size/d' => 15,
            'sort_field' => 'product_group_id',
            'sort_order' => 'desc',
            'shop_id' => request()->shopId
        ], 'get');
        $filterResult = $this->productGroupService->getFilterResult($filter);
        $total = $this->productGroupService->getFilterCount($filter);

        return $this->success([
            'filter_result' => $filterResult,
            'filter' => $filter,
            'total' => $total,
        ]);
    }

    /**
     * 详情页面
     *
     * @return \think\Response
     */
    public function detail(): \think\Response
    {
        $id = input('id/d');
        $item = $this->productGroupService->getDetail($id);

        return $this->success([
            'item' => $item,
        ]);
    }

    /**
     * 添加
     *
     * @return \think\Response
     */
    public function create(): \think\Response
    {
        $data = $this->requestData();
        $this->productGroupService->create($data);
        return $this->success('商品分组添加成功');
    }

    /**
     * 执行更新
     *
     * @return \think\Response
     */
    public function update(): \think\Response
    {
        $id = input('id/d', 0);
        $data = $this->requestData();
        $this->productGroupService->edit($id, $data);
        return $this->success('商品分组更新成功');
    }

    /**
     * 获取请求数据
     *
     * @return array
     */
    private function requestData(): array
    {
        $data = $this->request->only([
            'product_group_name' => '',
            'product_group_sn' => '',
            'product_group_description' => '',
            'product_ids' => [],
        ], 'post');
        $data['shop_id'] = request()->shopId;
        return $data;
    }


    /**
     * 删除
     *
     * @return \think\Response
     */
    public function del(): \think\Response
    {
        $id = input('id/d');

        if ($id) {
            $this->productGroupService->delete($id);
            return $this->success('指定项目已删除');
        } else {
            return $this->error('#id 错误');
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
                $this->productGroupService->delete($id);
            }
            return $this->success('批量操作执行成功！');
        } else {
            return $this->error('#type 错误');
        }
    }

}
