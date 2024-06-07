<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 账户添加
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
use app\service\api\admin\merchant\merchantAccountService;
use app\service\api\admin\merchant\ShopService;
use app\service\api\admin\user\UserService;
use think\App;
use think\facade\Db;
use think\Response;

/**
 * 商户银行账户控制器
 */
class Account extends AdminBaseController
{
    protected MerchantAccountService $merchantAccountService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param merchantAccountService $storeService
     */
    public function __construct(App $app, MerchantAccountService $merchantAccountService)
    {
        parent::__construct($app);
        $this->merchantAccountService = $merchantAccountService;
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
        ], 'get');
        $filter['merchant_id'] = request()->merchantId;
        $filterResult = $this->merchantAccountService->getFilterList($filter);
        $total = $this->merchantAccountService->getFilterCount($filter);

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
        $list = \app\model\merchant\MerchantAccount::TYPE_LIST;
        $typeList = [];
        foreach ($list as $k => $v) {
            $typeList[] = [
                'account_type' => $k,
                'account_type_text' => $v,
            ];
        }
        return $this->success([
            'account_type_list' => $typeList
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
        $item = $this->merchantAccountService->getDetail($id);
        $this->checkMerchantAuth($item['merchant_id']);
        return $this->success([
            'item' => $item,
        ]);
    }

    /**
     * 执行更新操作
     *
     * @return \think\Response
     */
    public function create(): \think\Response
    {
        $data = $this->request->only([
            'account_type' => '',
            'account_name' => '',
            'bank_name' => '',
            'account_no' => '',
            'bank_branch' => ''
        ], 'post');
        $data['merchant_id'] = request()->merchantId;
        $result = $this->merchantAccountService->update(0, $data, true);
        if ($result) {
            return $this->success(lang('账户添加更新成功'));
        } else {
            return $this->error('账户添加更新失败');
        }
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
            'account_id' => $id,
            'account_type' => '',
            'account_name' => '',
            'bank_name' => '',
            'account_no' => '',
            'bank_branch' => ''
        ], 'post');
        $item = $this->merchantAccountService->getDetail($id);
        $this->checkMerchantAuth($item['merchant_id']);
        $result = $this->merchantAccountService->update($id, $data, false);
        if ($result) {
            return $this->success(lang('账户添加更新成功'));
        } else {
            return $this->error('账户添加更新失败');
        }
    }

    /**
     * 删除
     *
     * @return \think\Response
     */
    public function del(): \think\Response
    {
        $id = input('id/d', 0);
        $this->merchantAccountService->delete($id);
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
                $this->merchantAccountService->delete($id);
            }
            return $this->success('批量操作执行成功！');
        } else {
            return $this->error('#type 错误');
        }
    }
}
