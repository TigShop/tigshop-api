<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 访问日志
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\sys;

use think\Model;
use utils\Time;

class AccessLog extends Model
{
    protected $pk = 'id';
    protected $table = 'access_log';
    protected $createTime = "access_time";
    protected $autoWriteTimestamp = true;

    // 访问时间检索
    public function scopeAccessTime($query, $value)
    {
        if (!empty($value)) {
            $value = is_array($value) ? $value : explode(',', $value);
            list($start_date, $end_date) = $value;
            $start_date = Time::toTime($start_date);
            $end_date = Time::toTime($end_date) + 86400;
            $value = [$start_date, $end_date];
            return $query->whereTime('access_time', "between", $value);
        }
    }

    // 根据店铺检索
    public function scopeshopId($query)
    {
        return $query->where('shop_id', request()->shopId);
    }

    // 平台访问检索
    public function scopeStorePlatform($query)
    {
        if (request()->shopId > 0) {
            return $query->where('shop_id', request()->shopId);
        } else {
            return $query;
        }
    }
}
