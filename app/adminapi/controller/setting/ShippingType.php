<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 配送类型
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\adminapi\controller\setting;

use app\adminapi\AdminBaseController;
use app\service\api\admin\setting\ShippingTypeService;
use app\validate\setting\ShippingTypeValidate;
use exceptions\ApiException;
use think\App;
use think\exception\ValidateException;
use think\facade\Db;
use think\Response;

/**
 * 配送类型控制器
 */
class ShippingType extends AdminBaseController
{
    protected ShippingTypeService $shippingTypeService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param ShippingTypeService $shippingTypeService
     */
    public function __construct(App $app, ShippingTypeService $shippingTypeService)
    {
        parent::__construct($app);
        $this->shippingTypeService = $shippingTypeService;
        $this->checkAuthor('shippingTypeManage'); //权限检查
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
            'paging' => true,
            'sort_field' => 'shipping_type_id',
            'sort_order' => 'desc',
        ], 'get');

        $filterResult = $this->shippingTypeService->getFilterResult($filter);
        $total = $this->shippingTypeService->getFilterCount($filter);

        return $this->success([
            'filter_result' => $filterResult,
            'filter' => $filter,
            'total' => $total,
        ]);
    }

    /**
     * 详情
     * @return Response
     */
    public function detail(): Response
    {
        $id = input('id/d', 0);
        $item = $this->shippingTypeService->getDetail($id);
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
            'shipping_type_name' => '',
            'shipping_default_id' => '',
            'is_support_cod' => '',
            'shipping_type_desc' => '',
            'shipping_time_desc' => '',
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
            validate(ShippingTypeValidate::class)
                ->scene('create')
                ->check($data);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        $result = $this->shippingTypeService->createShippingType($data);
        if ($result) {
            return $this->success(/** LANG */'配送类型添加成功');
        } else {
            return $this->error(/** LANG */'配送类型添加失败');
        }
    }

    /**
     * 执行更新操作
     *
     * @return Response
     */
    public function update(): Response
    {
        $id = input('id/d', 0);
        $data = $this->requestData();
        $data["shipping_type_id"] = $id;

        try {
            validate(ShippingTypeValidate::class)
                ->scene('update')
                ->check($data);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        $result = $this->shippingTypeService->updateShippingType($id, $data);
        if ($result) {
            return $this->success(/** LANG */'配送类型更新成功');
        } else {
            return $this->error(/** LANG */'配送类型更新失败');
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

        if (!in_array($field,
            ['shipping_type_name', 'sort_order', 'is_show', 'shipping_type_desc', 'is_support_cod'])) {
            return $this->error(/** LANG */'#field 错误');
        }

        $data = [
            'shipping_type_id' => $id,
            $field => input('val'),
        ];

        $this->shippingTypeService->updateShippingType($id, $data);

        return $this->success(/** LANG */'更新成功');
    }

    /**
     * 删除
     * @return Response
     */
    public function del(): Response
    {
        $id = input('id/d', 0);
        $this->shippingTypeService->deleteShippingType($id);
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
                    $this->shippingTypeService->deleteShippingType($id);
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
