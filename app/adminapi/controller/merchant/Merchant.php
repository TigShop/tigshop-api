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
use think\App;
use think\facade\Db;

/**
 * 商户控制器
 */
class Merchant extends AdminBaseController
{
    protected MerchantService $merchantService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param ApplyService $storeService
     */
    public function __construct(App $app, MerchantService $merchantService)
    {
        parent::__construct($app);
        $this->merchantService = $merchantService;
        $this->checkAuthor('merchantManage'); //权限检查
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

        $filterResult = $this->merchantService->getFilterList($filter, ['user']);
        $total = $this->merchantService->getFilterCount($filter);

        return $this->success([
            'filter_result' => $filterResult,
            'filter' => $filter,
            'total' => $total,
        ]);
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

        if (!in_array($field, ['status'])) {
            return $this->error('#field 错误');
        }

        $data = [
            $field => input('val'),
        ];

        $this->merchantService->updateField($id, $data);

        return $this->success('更新成功');
    }


    /**
     * 详情
     *
     * @return \think\Response
     */
    public function detail(): \think\Response
    {

        $id = input('id/d', 0);
        $item = $this->merchantService->getDetail($id);
        return $this->success([
            'item' => $item,
        ]);
    }


}
