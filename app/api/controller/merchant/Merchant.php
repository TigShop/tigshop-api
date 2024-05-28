<?php

namespace app\api\controller\merchant;

use app\api\IndexBaseController;
use app\service\api\index\merchant\ApplyService;
use think\App;

class Merchant extends IndexBaseController
{
    protected ApplyService $applyService;

    /**
     * 构造函数
     *
     * @param App $app
     */
    public function __construct(App $app, ApplyService $applyService)
    {
        $this->applyService = $applyService;
        parent::__construct($app);
        $this->checkLogin();
    }

    /**
     * 申请入驻
     * @return \think\Response
     */
    public function apply()
    {
        $data = $this->request->only([
            'shop_name' => '',
            'apply_data' => '',
            'type' => 1,
        ], 'post');
        $data['user_id'] = request()->userId;
        $this->applyService->createApply($data);
        return $this->success(/** LANG */ '入驻申请成功，请等待审核结果！');
    }

}