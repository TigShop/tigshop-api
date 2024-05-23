<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 运费模板
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\admin\controller\setting;

use app\admin\AdminBaseController;
use app\service\api\admin\setting\ShippingTplService;
use app\validate\setting\ShippingTplValidate;
use exceptions\ApiException;
use think\App;
use think\exception\ValidateException;
use think\facade\Db;
use think\Response;

/**
 * 运费模板控制器
 */
class ShippingTpl extends AdminBaseController
{
    protected ShippingTplService $shippingTplService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param ShippingTplService $shippingTplService
     */
    public function __construct(App $app, ShippingTplService $shippingTplService)
    {
        parent::__construct($app);
        $this->shippingTplService = $shippingTplService;
        $this->checkAuthor('shippingTplManage'); //权限检查
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
            'sort_field' => 'shipping_tpl_id',
            'sort_order' => 'desc',
        ], 'get');

        $filterResult = $this->shippingTplService->getFilterResult($filter);
        $total = $this->shippingTplService->getFilterCount($filter);

        return $this->success([
            'filter_result' => $filterResult,
            'filter' => $filter,
            'total' => $total,
        ]);
    }

    /**
     * 配置型
     * @return Response
     */
    public function config(): Response
    {
        $shipping_tpl_info = $this->shippingTplService->getShippingTplInfo();
        return $this->success([
            'shipping_tpl_info' => $shipping_tpl_info,
        ]);
    }

    /**
     * 详情
     * @return Response
     */
    public function detail(): Response
    {
        $id = input('id/d', 0);
        $shipping_tpl_info = $this->shippingTplService->getShippingTplInfo($id);
        $item = $this->shippingTplService->getDetail($id);
        $item['shipping_tpl_info'] = $shipping_tpl_info;
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
            'shipping_tpl_name' => '',
            'shipping_time' => '',
            'is_free/d' => 0,
            'pricing_type/d' => 1,
            'is_default/d' => 0,
            'shipping_tpl_info' => [],
        ], 'post');

        return $data;
    }

    /**
     * 添加操作
     * @return Response
     * @throws \exceptions\ApiException
     */
    public function create(): Response
    {
        $data = $this->requestData();

        try {
            validate(ShippingTplValidate::class)
                ->scene('create')
                ->check($data);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }
        $data["store_id"] = request()->storeId;
        $result = $this->shippingTplService->createShippingTpl($data);
        if ($result) {
            return $this->success(/** LANG */'运费模板添加成功');
        } else {
            return $this->error(/** LANG */'运费模板添加失败');
        }
    }

    /**
     * 执行更新操作
     * @return Response
     */
    public function update(): Response
    {
        $id = input('id/d', 0);
        $data = $this->requestData();
        $data["shipping_tpl_id"] = $id;

        try {
            validate(ShippingTplValidate::class)
                ->scene('update')
                ->check($data);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        $result = $this->shippingTplService->updateShippingTpl($id, $data);
        if ($result) {
            return $this->success(/** LANG */'运费模板更新成功');
        } else {
            return $this->error(/** LANG */'运费模板更新失败');
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

        if (!in_array($field, ['shipping_tpl_name', 'sort_order', 'is_show'])) {
            return $this->error(/** LANG */'#field 错误');
        }

        $data = [
            'shipping_tpl_id' => $id,
            $field => input('val'),
        ];

        $this->shippingTplService->updateShippingTplField($id, $data);

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
        $this->shippingTplService->deleteShippingTpl($id);
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
                    $this->shippingTplService->deleteShippingTpl($id);
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
