<?php

namespace app\service\api\index\merchant;

use app\model\merchant\Apply;
use app\service\core\BaseService;
use app\service\core\merchant\ApplyCoreService;

class ApplyService extends BaseService
{

    protected Apply $merchantApplyModel;
    protected ApplyCoreService $applyCoreService;


    public function __construct(Apply $merchantApplyModel, ApplyCoreService $applyCoreService)
    {
        $this->merchantApplyModel = $merchantApplyModel;
        $this->applyCoreService = $applyCoreService;
    }

    /**
     * 创建入驻申请
     * @param $data
     * @return Apply|\think\Model
     */
    public function createApply($data)
    {
        $result = $this->applyCoreService->create($data);
        return $result;
    }

    /**
     * 详情
     * @param $id
     * @return \app\model\merchant\Merchant|mixed
     */
    public function getDetail($id)
    {
        $result = $this->applyCoreService->getDetail($id);
        return $result;
    }

    public function getApplyByUserId(int $user_id)
    {
        return $this->merchantApplyModel->where('user_id', $user_id)->order('merchant_apply_id',
            'desc')->field(['merchant_apply_id,status,type'])->find();
    }



}