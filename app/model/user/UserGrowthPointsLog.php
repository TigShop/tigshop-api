<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 成长积分日志
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\user;

use think\Model;

class UserGrowthPointsLog extends Model
{
    protected $pk = 'log_id';
    protected $table = 'user_growth_points_log';
    protected $createTime = 'change_time';
    protected $autoWriteTimestamp = true;
}
