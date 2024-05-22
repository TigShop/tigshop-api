<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 余额充值
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
use app\service\api\admin\promotion\RechargeSettingService;
use think\App;
use think\facade\Db;
use think\Response;

/**
 * 余额充值控制器
 */
class RechargeSetting extends AdminBaseController
{
    protected RechargeSettingService $rechargeSettingService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param RechargeSettingService $rechargeSettingService
     */
    public function __construct(App $app, RechargeSettingService $rechargeSettingService)
    {
        parent::__construct($app);
        $this->rechargeSettingService = $rechargeSettingService;
        $this->checkAuthor('rechargeSettingManage'); //权限检查
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
            'sort_field' => 'recharge_id',
            'sort_order' => 'desc',
        ], 'get');

        $filterResult = $this->rechargeSettingService->getFilterResult($filter);
        $total = $this->rechargeSettingService->getFilterCount($filter);

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
        $item = $this->rechargeSettingService->getDetail($id);
        return $this->success([
            'item' => $item,
        ]);
    }

    /**
     * 添加
     * @return Response
     */
    public function create()
    {
        $data = $this->request->only([
            'money' => '',
            'discount_money' => '',
            'is_show' => '',
            'sort_order/d' => 50,
        ], 'post');

        $result = $this->rechargeSettingService->createRechargeSetting($data);
        if ($result) {
            return $this->success(/** LANG */'余额充值添加成功');
        } else {
            return $this->error(/** LANG */'余额充值添加失败');
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
            'recharge_id' => $id,
            'money' => '',
            'discount_money' => '',
            'is_show' => '',
            'sort_order/d' => 50,
        ], 'post');

        $result = $this->rechargeSettingService->updateRechargeSetting($id, $data);
        if ($result) {
            return $this->success(/** LANG */'余额充值更新成功');
        } else {
            return $this->error(/** LANG */'余额充值更新失败');
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

        if (!in_array($field, ['sort_order', 'is_show', 'money', 'discount_money'])) {
            return $this->error(/** LANG */'#field 错误');
        }

        $data = [
            'recharge_id' => $id,
            $field => input('val'),
        ];

        $this->rechargeSettingService->updateRechargeSettingField($id, $data);

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
        $this->rechargeSettingService->deleteRechargeSetting($id);
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
                    $this->rechargeSettingService->deleteRechargeSetting($id);
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
