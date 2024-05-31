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
            'merchant_data' => '',
            'base_data' => '',
            'shop_data' => '',
        ], 'post');
        $data['type'] = $data['shop_data']['type'];
        $data['user_id'] = request()->userId;
        $this->applyService->createApply($data);
        return $this->success(/** LANG */ '入驻申请成功，请等待审核结果！');
    }


    public function myApply(): \think\Response
    {
        $item = $this->applyService->getApplyByUserId(request()->userId);
        return $this->success([
            'item' => $item,
        ]);
    }

    public function applyDetail(): \think\Response
    {
        $id = input('id/d', 0);
        $item = $this->applyService->getDetail($id);
        return $this->success([
            'item' => $item,
        ]);
    }

}