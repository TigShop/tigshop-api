<?php

namespace app\service\api\admin\setting;

use app\common\exceptions\ApiException;
use app\common\log\AdminLog;
use app\common\utils\Config as UtilsConfig;
use app\common\utils\Util;
use app\model\setting\Config;
use app\service\api\admin\BaseService;

/**
 * 设置服务类
 */
class ConfigService extends BaseService
{
    protected Config $configModel;

    public function __construct(Config $configModel)
    {
        $this->configModel = $configModel;
    }

    /**
     * 获取后台相关的设置项
     *
     * @return array
     * @throws ApiException
     */
    public function getAdminConfig(): array
    {
        $config = UtilsConfig::getConfig('base');
        return [
            'ico_defined_css' => $config['ico_defined_css'],
            'dollar_sign' => $config['dollar_sign'],
            'storage_type' => $config['storage_type'],
            'storage_url' => UtilsConfig::getStorageUrl(),
            'pc_domain' => $config['pc_domain'],
            'h5_domain' => $config['h5_domain'],
        ];
    }

    /**
     * 获取指定的的设置项
     *
     * @param int $id
     * @return array
     */
    public function getConfig(string $code): ?array
    {
        return Config::where('code', $code)->value('data');
    }

    /**
     * 执行设置添加
     * @param string $code
     * @param array $data
     * @return int
     * @throws ApiException
     */
    public function createConfig(string $code, array $data): int
    {
        if (empty($code)) {
            throw new ApiException(/** LANG */'#code数据错误');
        }
        if (empty($data)) {
            throw new ApiException(/** LANG */'#data数据错误');
        }
        $config = Config::where('code', $code)->find();
        if(!empty($config)){
            throw new ApiException(/** LANG */'配置已存在，请勿重复添加！');
        }else{
            $result = Config::create(['code' => $code, 'data' => $data]);
            return $result->getKey();
        }
    }

    /**
     * 执行设置编辑
     * @param string $code
     * @param array $data
     * @return int
     * @throws ApiException
     */
    public function updateConfig(string $code, array $data): bool
    {
        if (empty($code)) {
            throw new ApiException(/** LANG */'#code数据错误');
        }
        if (empty($data)) {
            throw new ApiException(/** LANG */'#data数据错误');
        }
        $config = Config::where('code', $code)->find();

        if(empty($config)){
            throw new ApiException(/** LANG */'该配置不存在，请先添加配置！');
        }else{
            $config->data = $data;
            $config->save();
            return true;
        }
    }

    /**
     * 执行设置添加或更新
     *
     * @param string $code
     * @param array $data
     * @param bool $isAdd
     * @return bool
     * @throws ApiException
     */
    public function saveConfig(string $code, array $data): bool
    {
        if (empty($code)) {
            throw new ApiException(/** LANG */'#code数据错误');
        }
        if (!$data) {
            throw new ApiException(/** LANG */'#data数据错误');
        }

        $config = Config::where('code', $code)->find();
        if (!$config) {
            // 设置项不存在则新增
            Config::create(['code' => $code, 'data' => $data]);
        } else {
            // 更新
            $config->data = $data;
            $config->save();
        }

        AdminLog::add('更新设置:' . $code);
        return true;
    }

    /**
     * 发送测试邮件
     * @param string $data
     * @return bool
     * @throws ApiException
     */
    public function sendTestMail(string $data): bool
    {
        if (empty($data)) {
            throw new ApiException(/** LANG */'请输入邮件地址');
        }
        // 发送邮件
        $send_info = [
            'name' => '',
            'email' => $data,
            'subject' => '测试邮件',
            'content' => '这是一封测试邮件，收到此邮件代表着您的邮箱服务器设置正确！',
            'type' => 0,
        ];
        $result = Util::sendEmail($send_info);

        return $result;
    }

    public function registConfig()
    {

    }
}
