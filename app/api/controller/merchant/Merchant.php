<?php

namespace app\api\controller\merchant;

use app\api\IndexBaseController;
use app\service\api\index\merchant\ApplyService;
use think\App;
use utils\Config;

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
    }

    /**
     * 申请入驻
     * @return \think\Response
     */
    public function apply(): \think\Response
    {
        $data = $this->request->only([
            'shop_name' => '',
            'merchant_data' => '',
            'base_data' => '',
            'shop_data' => '',
        ], 'post');
        $data['type'] = $data['base_data']['type'];
        $data['company_name'] = $data['base_data']['company_name'] ?? '';
        $data['corporate_name'] = $data['base_data']['corporate_name'] ?? '';
        $data['user_id'] = request()->userId;
        $result = $this->applyService->createApply($data);
        return $this->success([
            'item' => $result
        ]);
    }

    /**
     * 申请入驻协议
     * @return \think\Response
     */
    public function applyShopAgreement(): \think\Response
    {
        return $this->success([
            'item' => Config::get('shop_agreement', 'merchant')
        ]);
    }

    /**
     * @return \think\Response
     */
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
        if ($item['user_id'] != request()->userId) {
            return $this->error('商户申请信息不存在');
        }
        return $this->success([
            'item' => $item,
        ]);
    }

}