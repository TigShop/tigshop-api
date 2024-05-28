<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 商品属性
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\adminapi\controller\product;

use app\adminapi\AdminBaseController;
use app\service\api\admin\product\ProductAttributesService;
use think\App;

/**
 * 商品属性控制器
 */
class ProductAttributes extends AdminBaseController
{
    protected ProductAttributesService $productAttributesService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param ProductAttributesService $productAttributesService
     */
    public function __construct(App $app, ProductAttributesService $productAttributesService)
    {
        parent::__construct($app);
        $this->productAttributesService = $productAttributesService;
        //$this->checkAuthor('productAttributesManage'); //权限检查
    }

    /**
     * 列表页面
     *
     * @return \think\Response
     */
    public function list(): \think\Response
    {
        $filter = $this->request->only([
            'is_show' => -1,
            'keyword' => '',
            'page' => 1,
            'size' => 15,
            'sort_field' => 'attribute_id',
            'sort_order' => 'desc',
        ], 'get');

        $filterResult = $this->productAttributesService->getFilterResult($filter);
        $total = $this->productAttributesService->getFilterCount($filter);

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
        $item = $this->productAttributesService->getDetail($id);
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
        $data = $this->request->only([
            'attr_name' => '',
            'is_show' => 1,
            'sort_order' => 50,
        ], 'post');

        $result = $this->productAttributesService->updateProductAttributes(0, $data, true);
        if ($result) {
            return $this->success('商品属性添加成功');
        } else {
            return $this->error('商品属性更新失败');
        }
    }

    /**
     * 更新操作
     *
     * @return \think\Response
     */
    public function update(): \think\Response
    {
        $id = input('id/d', 0);
        $data = $this->request->only([
            'attribute_id' => $id,
            'attr_name' => '',
            'is_show' => 1,
            'sort_order' => 50,
        ], 'post');

        $result = $this->productAttributesService->updateProductAttributes($id, $data, false);
        if ($result) {
            return $this->success('商品属性更新成功');
        } else {
            return $this->error('商品属性更新失败');
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

        if (!in_array($field, ['attr_name', 'is_show', 'sort_order'])) {
            return $this->error('#field 错误');
        }

        $data = [
            'attribute_id' => $id,
            $field => input('val'),
        ];

        $this->productAttributesService->updateProductAttributesField($id, $data);

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
        $this->productAttributesService->deleteProductAttributes($id);
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
                $this->productAttributesService->deleteProductAttributes($id);
            }
            return $this->success('批量操作执行成功！');
        } else {
            return $this->error('#type 错误');
        }
    }
}
