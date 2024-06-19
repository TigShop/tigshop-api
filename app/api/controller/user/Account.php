<?php

namespace app\api\controller\user;

use app\api\IndexBaseController;
use app\service\api\admin\finance\UserBalanceLogService;
use think\App;
use think\Response;

class Account extends IndexBaseController
{
    /**
     * @param App $app
     * @throws \exceptions\ApiException
     */
    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    /**
     * 账户金额变动列表
     * @return Response
     */
    public function list(): Response
    {
        $filter = $this->request->only([
            'page' => 1,
            'size' => 15,
            'sort_field' => 'log_id',
            'sort_order' => 'DESC',
            'balance' => true,
        ], 'get');
        $filter["user_id"] = request()->userId;
        $filterResult = app(UserBalanceLogService::class)->getFilterResult($filter);
        $total = app(UserBalanceLogService::class)->getFilterCount($filter);
        return $this->success([
            'filter_result' => $filterResult,
            'filter' => $filter,
            'total' => $total,
        ]);
    }
}