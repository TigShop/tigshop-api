<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 地区管理
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\adminapi\controller\setting;

use app\adminapi\AdminBaseController;
use app\service\api\admin\setting\RegionService;
use app\validate\setting\RegionValidate;
use exceptions\ApiException;
use think\App;
use think\exception\ValidateException;
use think\facade\Db;
use think\Response;

/**
 * 地区管理控制器
 */
class Region extends AdminBaseController
{
    protected RegionService $regionService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param RegionService $regionService
     */
    public function __construct(App $app, RegionService $regionService)
    {
        parent::__construct($app);
        $this->regionService = $regionService;
        $this->checkAuthor('regionManage'); //权限检查
    }

    /**
     * 列表页面
     *
     * @return Response
     */
    public function list(): Response
    {
        $filter = $this->request->only([
            'keyword' => '',
            'page/d' => 1,
            'size/d' => 15,
            'parent_id' => '',
            'region_id' => '',
            'sort_field' => 'region_id',
            'sort_order' => 'asc',
        ], 'get');

        $filterResult = $this->regionService->getFilterResult($filter);
        $total = $this->regionService->getFilterCount($filter);

        return $this->success([
            'filter_result' => $filterResult,
            'filter' => $filter,
            'total' => $total,
        ]);
    }

    /**
     * 添加或编辑页面
     *
     * @return Response
     */
    public function detail(): Response
    {
        $id = input('id/d', 0);
        $item = $this->regionService->getDetail($id);
        return $this->success([
            'item' => $item,
        ]);
    }

    /**
     * 获取地区树
     * @return Response
     */
    public function getRegionTree(): Response
    {
        $region_ids = input('region_ids', []);
        $region_tree = $this->regionService->getRegionTree($region_ids);

        return $this->success([
            'data' => $region_tree,
        ]);
    }

    /**
     * 获取所有地区树
     * @return Response
     */
    public function getAllRegionTree(): Response
    {
        $region_tree = $this->regionService->getAllRegionTree();
        return $this->success([
            'data' => $region_tree,
        ]);
    }

    /**
     * 获取子地区
     * @return Response
     */
    public function getChildRegion(): Response
    {
        $id = input('id');
        $region_tree = $this->regionService->getChildRegion($id);

        return $this->success([
            'data' => $region_tree,
        ]);
    }

    /**
     * 执行添加操作
     * @return Response
     */
    public function create(): Response
    {
        $data = $this->request->only([
            'region_name' => '',
            'parent_id' => '',
            'is_hot' => '',
            "first_word" => '',
        ], 'post');

        try {
            validate(RegionValidate::class)
                ->scene('create')
                ->check($data);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        $result = $this->regionService->createRegion($data);
        if ($result) {
            return $this->success(/** LANG */'地区管理添加成功');
        } else {
            return $this->error(/** LANG */'地区管理更新失败');
        }
    }

    /**
     * 执行更新操作
     * @return Response
     */
    public function update(): Response
    {
        $id = input('id/d', 0);
        $data = $this->request->only([
            'region_id' => $id,
            'region_name' => '',
            'parent_id' => '',
            'is_hot' => '',
            "first_word" => '',
        ], 'post');

        try {
            validate(RegionValidate::class)
                ->scene('update')
                ->check($data);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        $result = $this->regionService->updateRegion($id, $data);
        if ($result) {
            return $this->success(/** LANG */'地区管理更新成功');
        } else {
            return $this->error(/** LANG */'地区管理更新失败');
        }
    }

    /**
     * 更新单个字段
     *
     * @return Response
     */
    public function updateField(): Response
    {
        $id = input('id/d', 0);
        $field = input('field', '');

        if (!in_array($field, ['region_name', 'sort_order', 'is_hot', 'first_word'])) {
            return $this->error(/** LANG */'#field 错误');
        }

        $data = [
            'region_id' => $id,
            $field => input('val'),
        ];

        $this->regionService->updateRegionField($id, $data);

        return $this->success(/** LANG */'更新成功');
    }

    /**
     * 删除
     *
     * @return Response
     */
    public function del(): Response
    {
        $id = input('id/d', 0);
        $this->regionService->deleteRegion($id);
        return $this->success(/** LANG */'指定项目已删除');
    }

    /**
     * 批量操作
     * @return Response
     */
    public function batch(): Response
    {
        if (empty(input('ids')) || !is_array(input('ids'))) {
            return $this->error(/** LANG */'未选择项目');
        }

        if (input('type') == 'del') {
            try {
                //批量操作一定要事务
                Db::startTrans();
                foreach (input('ids') as $key => $id) {
                    $id = intval($id);
                    $this->regionService->deleteRegion($id);
                }
                Db::commit();
            } catch (\Exception $exception) {
                Db::rollback();
                throw new ApiException($exception->getMessage());
            }

            return $this->success(/** LANG */'批量操作执行成功！');
        } else {
            return $this->error(/** LANG */'#type 错误');
        }
    }
}
