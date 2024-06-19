<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 分类
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\adminapi\controller\merchant;

use app\adminapi\AdminBaseController;
use app\service\api\admin\merchant\ShopProductCategoryService;
use app\service\api\admin\product\CategoryService;
use log\AdminLog;
use think\App;

/**
 * 分类控制器
 */
class ShopProductCategory extends AdminBaseController
{
    protected ShopProductCategoryService $categoryService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param ShopProductCategoryService $categoryService
     */
    public function __construct(App $app, ShopProductCategoryService $shopProductCategoryService)
    {
        parent::__construct($app);
        $this->categoryService = $shopProductCategoryService;
    }

    /**
     * 列表页面
     *
     * @return \think\Response
     */
    public function list(): \think\Response
    {
        $filter = $this->request->only([
            'parent_id' => 0,
            'page' => 1,
            'size' => 15,
            'sort_field' => 'c.category_id',
            'sort_order' => 'asc',
        ], 'get');
        $filter['shop_id'] = request()->shopId;
        $filterResult = $this->categoryService->getFilterResult($filter);
        $total = $this->categoryService->getFilterCount($filter);

        if ($filter['parent_id'] > 0) {
            $parent_name = $this->categoryService->getName($filter['parent_id']);
        } else {
            $parent_name = null;
        }

        return $this->success([
            'filter_result' => $filterResult,
            'filter' => $filter,
            'total' => $total,
            'parent_name' => $parent_name,
        ]);
    }

    /**
     * 详情
     *
     * @return \think\Response
     */
    public function detail(): \think\Response
    {

        $id = input('id/d');
        $item = $this->categoryService->getDetail($id);
        $this->checkShopAuth($item['shop_id']);
        return $this->success([
            'item' => $item,
        ]);
    }

    /**
     * 执行更新
     *
     * @return \think\Response
     */
    public function update(): \think\Response
    {
        $id = input('category_id/d', 0);
        $data = $this->request->only([
            'category_id' => $id,
            'category_name' => '',
            'parent_id' => 0,
            'sort_order' => 50,
            'is_show' => 1,
        ], 'post');
        if ($data['category_id'] == $data['parent_id']) {
            return $this->error(/** LANG */ '上级不能选自己');
        }
        $item = $this->categoryService->getDetail($id);
        $this->checkShopAuth($item['shop_id']);
        $result = $this->categoryService->updateCategory($id, $data, false);
        if ($result) {
            return $this->success('分类更新成功');
        } else {
            return $this->error('分类更新失败');
        }
    }

    /**
     * 获取所有分类
     *
     * @return \think\Response
     */
    public function getAllCategory()
    {
        $data = $this->request->only([
            'shop_id' => 0,
        ], 'get');
        if (request()->adminType = 'shop') {
            $data['shop_id'] = request()->shopId;
        }
        $cat_list = $this->categoryService->catList(0, $data['shop_id']);
        return $this->success([
            'filter_result' => $cat_list,
        ]);
    }

    /**
     * 更新单个字段
     *
     * @return \think\Response
     */
    public function updateField(): \think\Response
    {
        $id = input('id/d');
        $field = input('field');

        if (!in_array($field, ['category_name', 'measure_unit', 'is_hot', 'is_show', 'sort_order'])) {
            return $this->error('#field 错误');
        }
        $item = $this->categoryService->getDetail($id);
        $this->checkShopAuth($item['shop_id']);
        $data = [
            'category_id' => $id,
            $field => input('val'),
        ];

        $this->categoryService->updateCategoryField($id, $data);

        return $this->success('更新成功');
    }


    /**
     * 执行新增
     *
     * @return \think\Response
     */
    public function create(): \think\Response
    {
        $data = $this->request->only([
            'category_name' => '',
            'parent_id' => 0,
            'sort_order' => 50,
            'is_show' => 1,
        ], 'post');
        $data['shop_id'] = request()->shopId;
        $result = $this->categoryService->updateCategory(0, $data, true);
        if ($result) {
            AdminLog::add('新增分类：' . $data['category_name']);
            return $this->success('分类添加成功');
        } else {
            return $this->error('分类更新失败');
        }
    }

    /**
     * 删除
     *
     * @return \think\Response
     */
    public function del(): \think\Response
    {
        $id = input('id/d');
        $item = $this->categoryService->getDetail($id);
        $this->checkShopAuth($item['shop_id']);
        if ($id) {
            $this->categoryService->deleteCategory($id);
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
                $item = $this->categoryService->getDetail($id);
                $this->checkShopAuth($item['shop_id']);
                $id = intval($id);
                $this->categoryService->deleteCategory($id);
            }

            return $this->success('批量操作执行成功！');
        } else {
            return $this->error('#type 错误');
        }
    }
}
