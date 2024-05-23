<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 计划任务
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\setting;

use think\Model;
use utils\Time;

class Crons extends Model
{
    protected $pk = 'cron_id';
    protected $table = 'crons';
    protected $json = ['cron_config'];
    protected $jsonAssoc = true;

    // 定时方式
    const CRON_TYPE_YEAR = 1;
    const CRON_TYPE_MONTH = 2;
    const CRON_TYPE_WEEK = 3;
    const CRON_TYPE_DAY = 4;
    const CRON_TYPE_HOUR = 5;
    const CRON_TYPE_MINUTE = 6;
    const CRON_TYPE_N_YEAR = 11;
    const CRON_TYPE_N_MONTH = 12;
    const CRON_TYPE_N_WEEK = 13;
    const CRON_TYPE_N_DAY = 14;
    const CRON_TYPE_N_HOUR = 15;
    const CRON_TYPE_N_MINUTE = 16;
    const CRON_TYPE_NAME = [
        self::CRON_TYPE_YEAR => '每年',
        self::CRON_TYPE_MONTH => '每月',
        self::CRON_TYPE_WEEK => '每周',
        self::CRON_TYPE_DAY => '每日',
        self::CRON_TYPE_HOUR => '每小时',
        self::CRON_TYPE_MINUTE => '每分钟',
        self::CRON_TYPE_N_YEAR => '每隔N年',
        self::CRON_TYPE_N_MONTH => '每隔N月',
        self::CRON_TYPE_N_WEEK => '每隔N周',
        self::CRON_TYPE_N_DAY => '每隔N日',
        self::CRON_TYPE_N_HOUR => '每隔N小时',
        self::CRON_TYPE_N_MINUTE => '每隔N分钟',
    ];

    // 上次执行时间
    public function getLastRunTimeAttr($value)
    {
        return Time::format($value);
    }

    // 下次执行时间
    public function getNextRunTimeAttr($value)
    {
        return Time::format($value);
    }

    // 定时方式
    public function getCronTypeNameAttr($value, $data): string
    {
        return self::CRON_TYPE_NAME[$data['cron_type']] ?? '';
    }

}
