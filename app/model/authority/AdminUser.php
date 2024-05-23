<?php
//**---------------------------------------------------------------------+
//**   分类模型
//**---------------------------------------------------------------------+
//**   版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//**   作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//**   提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\authority;

use think\Model;
use utils\Time;

class AdminUser extends Model
{
    protected $pk = 'admin_id';
    protected $table = 'admin_user';
    protected $json = ['extra', 'auth_list', 'menu_tag', 'order_export'];
    // 设置JSON数据返回数组
    protected $jsonAssoc = true;
    protected $createTime = "add_time";
    protected $autoWriteTimestamp = true;

    // 关联角色
    public function role()
    {
        return $this->hasOne(AdminRole::class, 'role_id', 'role_id')->bind(["role_name"]);
    }

    // 添加时间
    public function getAddTimeAttr($value)
    {
        return Time::format($value);
    }
}
