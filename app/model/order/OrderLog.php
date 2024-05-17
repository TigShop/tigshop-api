<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 订单日志
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\order;

use app\common\utils\Time;
use app\model\authority\AdminUser;
use app\model\user\User;
use think\Model;

class OrderLog extends Model
{
    protected $pk = 'log_id';
    protected $table = 'order_log';

    //关联管理员
    public function adminUser()
    {
        return $this->hasOne(AdminUser::class, 'admin_id', 'admin_id')->bind(["username"]);
    }
    public function getLogTimeAttr($value): string
    {
        return Time::format($value);
    }

    // 操作者
    public function getOperatorAttr($value, $data): string
    {
        if ($data["admin_id"] > 0) {
            return AdminUser::where("admin_id", $data["admin_id"])->value("username");
        } else {
            return User::where("user_id", $data["user_id"])->value("username");
        }
    }
}
