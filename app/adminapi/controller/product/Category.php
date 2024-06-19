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

namespace app\adminapi\controller\product;

use app\adminapi\AdminBaseController;
use app\service\api\admin\product\CategoryService;
use app\validate\product\CategoryValidate;
use log\AdminLog;
use think\App;
use think\exception\ValidateException;
use tig\CacheManager;

/**
 * 分类控制器
 */
class Category extends AdminBaseController
{
    protected CategoryService $categoryService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param CategoryService $categoryService
     */
    public function __construct(App $app, CategoryService $categoryService)
    {
        parent::__construct($app);
        $this->categoryService = $categoryService;
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
     * 商品转移
     *
     * @return \think\Response
     */
    public function moveCat(): \think\Response
    {
        $id = input('id/d', 0);
        $target_cat_id = input('target_cat_id/d', 0);

        $this->categoryService->moveCat($id, $target_cat_id);
        /* 清除分类缓存 */
        app(CacheManager::class)->clearCacheByTag('cat');

        return $this->success('商品转移成功！');
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

        return $this->success([
            'item' => $item,
        ]);
    }

    /**
     * 获取请求数据
     * @return array
     */
    public function requestData(): array
    {
        $data = $this->request->only([
            'category_name' => '',
            'short_name' => '',
            'parent_id' => 0,
            'category_pic' => '',
            'category_ico' => '',
            'measure_unit' => '',
            'seo_title' => '',
            'search_keywords' => '',
            'keywords' => '',
            'category_desc' => '',
            'is_hot' => 0,
            'is_show' => 0,
            'sort_order' => 50,
        ], 'post');

        return $data;
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
        $data['category_id'] = $id;

        try {
            validate(CategoryValidate::class)
                ->scene('update')
                ->check($data);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        if ($data['category_id'] == $data['parent_id']) {
            return $this->error(/** LANG */ '上级不能选自己');
        }
        $result = $this->categoryService->updateCategory($id, $data);
        if ($result) {
            AdminLog::add('编辑分类：' . $data['category_name']);
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
        $cat_list = $this->categoryService->catList();
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
        $data = $this->requestData();
        try {
            validate(CategoryValidate::class)
                ->scene('create')
                ->check($data);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        $result = $this->categoryService->createCategory($data);
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
                $id = intval($id);
                $this->categoryService->deleteCategory($id);
            }

            return $this->success('批量操作执行成功！');
        } else {
            return $this->error('#type 错误');
        }
    }
}
