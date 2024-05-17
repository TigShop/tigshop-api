<?php

namespace app\common\utils;

use app\service\setting\ConfigService;

class Config
{

    protected static array $config = [];

    /**
     * 获取参数
     *
     * @param string $name
     * @param string $code
     * @param $default
     * @return int|string|array|null
     */
    public static function get(string $name = '', string $code = 'base', $default = null): int | string | array | null
    {
        $config = self::getConfig($code);
        return isset($config[$name]) ? $config[$name] : $default;
    }

    /**
     * 获取配置
     *
     * @param string $name
     * @param string $code
     * @return int|string|array
     */
    public static function getConfig(string $code): int | string | array
    {
        if (!isset(self::$config[$code])) {
            self::$config[$code] = app(ConfigService::class)->getConfig($code);
        }
        return self::$config[$code];
    }

    public static function getStorageUrl(): string
    {
        $storage_type = self::get('storage_type');
        $storage_url = '';
        switch ($storage_type) {
            case 0:
                $storage_url = self::get('storage_local_url');
                break;
            case 1:
                $storage_url = self::get('storage_oss_url');
                break;
            case 2:
                $storage_url = self::get('storage_cos_url');
                break;
            default:
                $storage_url = '';
        }
        return $storage_url;
    }

}
