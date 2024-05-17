<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 供应商
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\authority;

use think\Model;

class Suppliers extends Model
{
    protected $pk = 'suppliers_id';
    protected $table = 'suppliers';

    public function adminUser()
    {
        return $this->hasMany(AdminUser::class, 'suppliers_id', 'suppliers_id');
    }

    // 区域数组格式
    public function getRegionsAttr($value, $data)
    {
        return [$data["province"], $data["city"], $data["district"]];
    }
}
