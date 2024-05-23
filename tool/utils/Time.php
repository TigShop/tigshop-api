<?php

namespace utils;

class Time
{
    /**
     * 获取当前的时间戳
     *
     * @return int
     */
    public static function now()
    {
        return time();
    }

    /**
     * 获取今天的时间戳
     *
     * @return int
     */
    public static function today()
    {
        return strtotime('today');
    }
    /**
     * 获取指定时间的n天前的时间戳
     *
     * @return int
     */
    public static function daysAgo(int $days, int $timestamp = 0)
    {
        $timestamp = empty($timestamp) ? self::now() : $timestamp;
        return strtotime('-' . $days . ' days',$timestamp);
    }

    /**
     * 获取n个月前的时间戳
     *
     * @return int
     */
    public static function monthAgo(int $month)
    {
        return strtotime('-' . $month . ' months');
    }

    /**
     * 格式化时间戳为指定格式的日期时间字符串
     *
     * @param int    $timestamp
     * @param string $format
     * @return string
     */
    public static function format($timestamp = null, $format = 'Y-m-d H:i:s')
    {
        if ($timestamp === NULL) {
            $timestamp = self::now();
        } elseif (!is_numeric($timestamp)) {
            return $timestamp;
        } elseif ($timestamp <= 0) {
            return '';
        }
        return date($format, $timestamp);
    }

    /**
     * 获取指定日期时间字符串的时间戳
     *
     * @param string $datetime
     * @return int|false
     */
    public static function toTime($datetime)
    {
        return strtotime($datetime);
    }

    /**
     * 获取当前的日期时间字符串
     *
     * @param string $format
     * @return string
     */
    public static function getCurrentDatetime($format = 'Y-m-d H:i:s')
    {
        return date($format);
    }

    /**
     * 判断是否是时间戳格式
     * @param $timestamp
     * @return false|int
     */
    public static function isTimestampFormat($timestamp) {
        $pattern = '/^\d{10}$/'; // 10位数字格式的时间戳
        return preg_match($pattern, $timestamp);
    }
}
