<?php

namespace app\service\core\merchant;

use app\model\merchant\Merchant;

/**
 * 商户管理核心服务
 */
class MerchantCoreService
{
    protected Merchant $merchantModel;

    public function __construct(Merchant $merchant)
    {
        $this->merchantModel = $merchant;
    }


    /**
     * 获取详情
     *
     * @param int $merchant_id
     * @return Merchant|mixed
     */
    public function getDetail(int $merchant_id, string $field = '*'): mixed
    {
        return $this->merchantModel->field($field)->where('merchant_id', $merchant_id)->find();
    }

    /**
     * 创建商户
     * @param array $data
     * @return Merchant|\think\Model
     */
    public function create(array $data): Merchant|\think\Model
    {
        $data['add_time'] = time();
        return $this->merchantModel->create($data);
    }

}