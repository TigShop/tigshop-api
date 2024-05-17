<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 退换货记录
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\order;

use think\Model;

class AftersalesLog extends Model
{
    protected $pk = 'log_id';
    protected $table = 'aftersales_log';
    protected $createTime = "add_time";
    protected $autoWriteTimestamp = true;
    protected $json = ['return_pic'];
    protected $jsonAssoc = true;
}
