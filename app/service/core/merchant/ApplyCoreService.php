<?php

namespace app\service\core\merchant;

use app\model\merchant\Apply;
use app\model\merchant\Merchant;

/**
 * 商户管理核心服务
 */
class ApplyCoreService
{
    protected Apply $applyModel;

    public function __construct(Apply $applyModel)
    {
        $this->applyModel = $applyModel;
    }


    /**
     * 获取详情
     *
     * @param int $merchant_id
     * @return Merchant|mixed
     */
    public function getDetail(int $merchant_id, string $field = '*'): mixed
    {
        return $this->applyModel->field($field)->findOrEmpty($merchant_id);
    }

    /**
     * 创建商户
     * @param array $data
     * @return Merchant|\think\Model
     */
    public function create(array $data): Merchant|\think\Model
    {
        $data['add_time'] = time();
        return $this->applyModel->create($data);
    }

}