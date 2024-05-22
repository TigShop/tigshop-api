<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 积分商品
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\admin\controller\promotion;

use app\admin\AdminBaseController;
use app\common\exceptions\ApiException;
use app\service\api\admin\promotion\PointsExchangeService;
use app\validate\promotion\PointsExchangeValidate;
use think\App;
use think\exception\ValidateException;
use think\facade\Db;
use think\Response;

/**
 * 积分商品控制器
 */
class PointsExchange extends AdminBaseController
{
    protected PointsExchangeService $pointsExchangeService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param PointsExchangeService $pointsExchangeService
     */
    public function __construct(App $app, PointsExchangeService $pointsExchangeService)
    {
        parent::__construct($app);
        $this->pointsExchangeService = $pointsExchangeService;
        $this->checkAuthor('pointsExchangeManage'); //权限检查
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
            'sort_field' => 'id',
            'sort_order' => 'desc',
            'is_enabled/d' => -1,
            'is_hot/d' => -1,
        ], 'get');

        $filterResult = $this->pointsExchangeService->getFilterResult($filter);
        $total = $this->pointsExchangeService->getFilterCount($filter);

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
        $item = $this->pointsExchangeService->getDetail($id);
        return $this->success([
            'item' => $item,
        ]);
    }

    /**
     * 添加
     * @return Response
     */
    public function create(): Response
    {
        $data = $this->request->only([
            'product_id' => '',
            'exchange_integral' => '',
            'points_deducted_amount' => '',
            'is_hot' => '',
            'is_enabled' => '',
        ], 'post');

        try {
            validate(PointsExchangeValidate::class)
                ->scene('create')
                ->check($data);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        $result = $this->pointsExchangeService->createPointsExchange($data);
        if ($result) {
            return $this->success(/** LANG */'积分商品添加成功');
        } else {
            return $this->error(/** LANG */'积分商品添加失败');
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
            'id' => $id,
            'product_id' => '',
            'exchange_integral' => '',
            'points_deducted_amount' => '',
            'is_hot' => '',
            'is_enabled' => '',
        ], 'post');

        try {
            validate(PointsExchangeValidate::class)
                ->scene('update')
                ->check($data);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        $result = $this->pointsExchangeService->updatePointsExchange($id, $data);
        if ($result) {
            return $this->success(/** LANG */'积分商品更新成功');
        } else {
            return $this->error(/** LANG */'积分商品更新失败');
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

        if (!in_array($field, ['exchange_integral', 'points_deducted_amount', 'is_hot', "is_enabled"])) {
            return $this->error(/** LANG */'#field 错误');
        }

        $data = [
            'id' => $id,
            $field => input('val'),
        ];

        $this->pointsExchangeService->updatePointsExchangeField($id, $data);

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
        $this->pointsExchangeService->deletePointsExchange($id);
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
                    $this->pointsExchangeService->deletePointsExchange($id);
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
