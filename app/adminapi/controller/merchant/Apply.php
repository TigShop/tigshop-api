<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 商户入驻申请
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\adminapi\controller\merchant;

use app\adminapi\AdminBaseController;
use app\service\api\admin\authority\AdminUserService;
use app\service\api\admin\merchant\ApplyService;
use app\service\api\admin\merchant\MerchantService;
use app\service\api\admin\merchant\ShopService;
use app\service\api\admin\user\UserService;
use think\App;
use think\facade\Db;
use think\Response;

/**
 * 商户申请控制器
 */
class Apply extends AdminBaseController
{
    protected ApplyService $applyService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param ApplyService $storeService
     */
    public function __construct(App $app, ApplyService $applyService)
    {
        parent::__construct($app);
        $this->applyService = $applyService;
        $this->checkAuthor('merchantApplyManage'); //权限检查
    }

    /**
     * 列表页面
     *
     * @return \think\Response
     */
    public function list(): \think\Response
    {
        $filter = $this->request->only([
            'page/d' => 1,
            'size/d' => 15,
            'sort_field' => 'add_time',
            'sort_order' => 'desc',
            'status' => -1,
            'username' => ''
        ], 'get');

        $filterResult = $this->applyService->getFilterResult($filter);
        $total = $this->applyService->getFilterCount($filter);

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
        $list = \app\model\merchant\Apply::STATUS_LIST;
        $status_list = [];
        foreach ($list as $k => $v) {
            $status_list[] = [
                'status' => $k,
                'status_text' => $v,
            ];
        }
        return $this->success([
            'status_list' => $status_list
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
        $item = $this->applyService->getDetail($id);
        return $this->success([
            'item' => $item,
        ]);
    }

    /**
     * 执行更新操作
     *
     * @return \think\Response
     */
    public function update(): \think\Response
    {
        $id = input('id/d', 0);
        $data = $this->request->only([
            'merchant_apply_id' => $id,
            'shop_name' => '',
            'status/d' => 1,
        ], 'post');

        $result = $this->applyService->update($id, $data, false);
        if ($result) {
            return $this->success(lang('商户入驻申请更新成功'));
        } else {
            return $this->error('商户入驻申请更新失败');
        }
    }

    /**
     * 执行审核操作
     *
     * @return \think\Response
     */
    public function audit(): \think\Response
    {
        $params = $this->request->only([
            'merchant_apply_id' => 0,
            'status/d' => 1,
            'audit_remark' => ''
        ], 'post');
        try {
            Db::startTrans();
            $item = $this->applyService->getDetail($params['merchant_apply_id']);
            $userInfo = app(UserService::class)->getDetail($item['user_id']);
            $result = $this->applyService->audit($params['merchant_apply_id'], $params['status'],
                $params['audit_remark']);
            if ($result && $params['status'] == 10) {
                $merchantService = app(MerchantService::class);
                $merchantDetail = $merchantService->create([
                    'merchant_apply_id' => $item['merchant_apply_id'],
                    'user_id' => $item['user_id'],
                    'type' => $item['type'],
                    'shop_data' => $item['shop_data'],
                    'base_data' => $item['base_data'],
                    'merchant_data' => $item['merchant_data'],
                    'company_name' => $item['company_name'],
                    'corporate_name' => $item['corporate_name'],
                ]);
                $adminId = app(AdminUserService::class)->createAdminUser([
                    'username' => $userInfo['mobile'],
                    'mobile' => $userInfo['mobile'],
                    'email' => $userInfo['email'],
                    'password' => '',
                    'admin_type' => 'shop',
                    'role_id' => 1,
                    'avatar' => '',
                    'pwd_confirm' => '',
                    'merchant_id' => $merchantDetail->merchant_id,
                ]);
                $merchantService->createUser([
                    'merchant_id' => $merchantDetail->merchant_id,
                    'user_id' => $item['user_id'],
                    'admin_user_id' => $adminId,
                ]);
                app(ShopService::class)->create([
                    'merchant_id' => $merchantDetail->merchant_id,
                    'shop_title' => $item['shop_name']
                ]);
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error($e->getMessage());
        }

        if ($result) {
            return $this->success('商户入驻申请审核操作成功');
        } else {
            return $this->error('商户入驻申请审核操作失败');
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

        if (!in_array($field, ['store_title', 'sort_order'])) {
            return $this->error('#field 错误');
        }

        $data = [
            'store_id' => $id,
            $field => input('val'),
        ];

        $this->applyService->updateField($id, $data);

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
        $this->applyService->delete($id);
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
                $this->applyService->delete($id);
            }
            return $this->success('批量操作执行成功！');
        } else {
            return $this->error('#type 错误');
        }
    }
}
