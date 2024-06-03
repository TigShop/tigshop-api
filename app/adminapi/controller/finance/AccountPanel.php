<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 账户资金
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\adminapi\controller\finance;

use app\adminapi\AdminBaseController;
use app\service\api\admin\finance\AccountPanelService;
use think\App;
use think\response\Json;

/**
 * 账户资金面板控制器
 */
class AccountPanel extends AdminBaseController
{
    protected AccountPanelService $accountPanelService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param AccountPanelService $accountPanelService
     */
    public function __construct(App $app, AccountPanelService $accountPanelService)
    {
        parent::__construct($app);
        $this->accountPanelService = $accountPanelService;
        $this->checkAuthor('accountPanel'); //权限检查
    }

    /**
     * 列表页面
     *
     * @return Json
     */
    public function list(): Json
    {
        $filter = $this->request->only([
            'search_start_date' => "",
            'search_end_date' => "",
        ], 'get');

        $filterResult = $this->accountPanelService->getFilterResult($filter);

        return $this->success([
            'filter_result' => $filterResult,
            'filter' => $filter,
        ]);
    }

}
