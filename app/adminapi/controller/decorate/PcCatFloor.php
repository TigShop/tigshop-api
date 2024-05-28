<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- PC分类抽屉
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\adminapi\controller\decorate;

use app\adminapi\AdminBaseController;
use app\service\api\admin\decorate\PcCatFloorService;
use app\validate\decorate\PcCatFloorValidate;
use exceptions\ApiException;
use think\App;
use think\exception\ValidateException;
use think\facade\Db;
use think\Response;

/**
 * PC分类抽屉控制器
 */
class PcCatFloor extends AdminBaseController
{
    protected PcCatFloorService $pcCatFloorService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param PcCatFloorService $pcCatFloorService
     */
    public function __construct(App $app, PcCatFloorService $pcCatFloorService)
    {
        parent::__construct($app);
        $this->pcCatFloorService = $pcCatFloorService;
        $this->checkAuthor('pcCatFloorManage'); //权限检查
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
            'is_show/d' => -1,
            'sort_field' => 'cat_floor_id',
            'sort_order' => 'desc',
        ], 'get');

        $filterResult = $this->pcCatFloorService->getFilterResult($filter);
        $total = $this->pcCatFloorService->getFilterCount($filter);

        return $this->success([
            'filter_result' => $filterResult,
            'filter' => $filter,
            'total' => $total,
        ]);
    }

    /**
     * 详情
     *
     * @return Response
     */
    public function detail(): Response
    {
        $id = input('id/d', 0);
        $item = $this->pcCatFloorService->getDetail($id);
        return $this->success([
            'item' => $item,
        ]);
    }

    /**
     * 请求数据
     * @return array
     */
    public function requestData(): array
    {
        $data = $this->request->only([
            'category_ids' => [],
            "category_names" => [],
            "floor_ico" => '',
            "hot_cat" => '',
            "is_show/d" => 1,
            "floor_ico_font" => '',
            "brand_ids" => [],
            'sort_order/d' => 50,
        ], 'post');

        return $data;
    }

    /**
     * 添加
     * @return Response
     */
    public function create(): Response
    {
        $data = $this->requestData();

        try {
            validate(PcCatFloorValidate::class)
                ->scene('create')
                ->check($data);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        $result = $this->pcCatFloorService->createPcCatFloor($data);
        if ($result) {
            return $this->success(/** LANG */'PC分类抽屉添加成功');
        } else {
            return $this->error(/** LANG */'PC分类抽屉添加失败');
        }
    }

    /**
     * 执行更新操作
     * @return Response
     */
    public function update()
    {
        $id = input('id/d', 0);
        $data = $this->requestData();
        $data["cat_floor_id"] = $id;
        try {
            validate(PcCatFloorValidate::class)
                ->scene('update')
                ->check($data);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        $result = $this->pcCatFloorService->updatePcCatFloor($id, $data);
        if ($result) {
            return $this->success(/** LANG */'PC分类抽屉更新成功');
        } else {
            return $this->error(/** LANG */'PC分类抽屉更新失败');
        }
    }

    /**
     * 更新单个字段
     * @return Response
     */
    public function updateField(): Response
    {
        $id = input('id/d', 0);
        $field = input('field', '');

        if (!in_array($field, ['cat_floor_name', 'sort_order', 'is_show'])) {
            return $this->error(/** LANG */'#field 错误');
        }

        $data = [
            'cat_floor_id' => $id,
            $field => input('val'),
        ];

        $this->pcCatFloorService->updatePcCatFloorField($id, $data);

        return $this->success(/** LANG */'更新成功');
    }

    /**
     * 删除
     * @return Response
     */
    public function del(): Response
    {
        $id = input('id/d', 0);
        $this->pcCatFloorService->deletePcCatFloor($id);
        return $this->success(/** LANG */'指定项目已删除');
    }

    /**
     * 批量操作
     *
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
                    $this->pcCatFloorService->deletePcCatFloor($id);
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
