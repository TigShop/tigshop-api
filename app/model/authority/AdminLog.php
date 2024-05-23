<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 管理员日志
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\authority;

use think\Model;
use utils\Time;

class AdminLog extends Model
{
    protected $pk = 'log_id';
    protected $table = 'admin_log';

    public function adminUser()
    {
        return $this->hasOne(AdminUser::class, 'admin_id', 'user_id')->bind(["username"]);
    }

    //日志时间
    public function getLogTimeAttr($value)
    {
        return Time::format($value);
    }
}
