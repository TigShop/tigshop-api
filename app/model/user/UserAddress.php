<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 收货人信息
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\user;

use think\Model;

class UserAddress extends Model
{
    protected $pk = 'address_id';
    protected $table = 'user_address';
    protected $json = ["region_ids", "region_names"];
    protected $jsonAssoc = true;

    public function getRegionNameAttr($value, $data)
    {
        if (isset($data['region_names'])) {
            if (isset($data['region_names'][1]) && $data['region_names'][0] == $data['region_names'][1]) {
                unset($data['region_names'][1]);
            }
            return implode(' ', $data['region_names']);
        } else {
            return '';
        }
    }
}
