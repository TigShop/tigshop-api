<?php

namespace app\service\api\index\merchant;

use app\model\merchant\Apply;
use app\service\api\admin\BaseService;

class ApplyService extends BaseService
{

    protected Apply $merchantApplyModel;

    public function __construct(Apply $merchantApplyModel)
    {
        $this->merchantApplyModel = $merchantApplyModel;
    }

    /**
     * 创建入驻申请
     * @param $data
     * @return Apply|\think\Model
     */
    public function createApply($data)
    {
        $result = $this->merchantApplyModel->create($data);
        return $result;
    }
}