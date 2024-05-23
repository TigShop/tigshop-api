<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 设置项
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\admin\controller\setting;

use app\admin\AdminBaseController;
use app\model\setting\Region;
use app\service\api\admin\file\FileStorage;
use app\service\api\admin\pay\CertificatesService;
use app\service\api\admin\setting\ConfigService;
use think\App;
use think\Response;
use utils\Config as ShopConfig;

/**
 * 设置项控制器
 */
class Config extends AdminBaseController
{
    protected ConfigService $configService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param ConfigService $configService
     */
    public function __construct(App $app, ConfigService $configService)
    {
        parent::__construct($app);
        $this->configService = $configService;
    }

    /**
     * 基础设置
     * @return Response
     */
    public function list(): Response
    {
        $code = input('code');
        $config = $this->configService->getConfig($code);
        if ($code == 'payment') {
            //检测证书状态
            $private_key = app()->getRootPath() . '/app/common/certs/wechat/apiclient_key.pem';
            $certificate = app()->getRootPath() . '/app/common/certs/wechat/apiclient_cert.pem';
            $platform_certs = app()->getRootPath() . '/app/common/certs/wechat/cert.pem';
            if (is_file($private_key)) {
                $config['wechat_pay_private_key'] = 1;
            }
            if (is_file($certificate)) {
                $config['wechat_pay_certificate'] = 1;
            }
            if (is_file($platform_certs)) {
                $config['wechat_pay_platform_certificate'] = 1;
            }
        }
        $result = [
            'item' => $config,
        ];
        if ($code == 'base') {
            $country = Region::where('parent_id', 0)->select();
            $result['countrys'] = $country ? $country->toArray() : [];
        }
        return $this->success($result);
    }

    /**
     * 基础设置更新
     * @return Response
     */
    public function save(): Response
    {
        $code = input('code');
        $default_config = \think\facade\Config::get('shop.' . $code);
        if (!$default_config) {
            return $this->error(/** LANG */'不存在的设置项');
        }
        $data = $this->request->only($default_config, 'post');
        $result = $this->configService->saveConfig($code, $data);
        if ($result) {
            return $this->success(/** LANG */'设置项更新成功');
        } else {
            return $this->error(/** LANG */'设置项更新失败');
        }
    }

    /**
     * 添加配置
     * @return Response
     * @throws \exceptions\ApiException
     */
    public function create(): Response
    {
        $code = input('code');
        $data = input("data/a", []);
        $result = $this->configService->createConfig($code, $data);
        return $result ? $this->success(/** LANG */'设置项添加成功') : $this->error(/** LANG */'设置项添加失败');
    }

    /**
     * 编辑配置
     * @return Response
     * @throws \exceptions\ApiException
     */
    public function update(): Response
    {
        $code = input('code');
        $data = input("data/a", []);
        $result = $this->configService->updateConfig($code, $data);
        return $result ? $this->success(/** LANG */'设置项更新成功') : $this->error(/** LANG */'设置项更新失败');
    }

    /**
     * 邮箱服务器设置
     * @return Response
     */
    public function saveMail(): Response
    {
        $code = input('code');
        $data = $this->request->only([
            'mail_service/d' => 0,
            'smtp_ssl/d' => 0,
            'smtp_host' => '',
            'smtp_port' => '',
            'smtp_user' => '',
            'smtp_pass' => '',
            'smtp_mail' => '',
            'mail_charset' => '',
            'test_mail_address' => '',
        ], 'post');
        $result = $this->configService->saveConfig($code, $data);
        if ($result) {
            return $this->success(/** LANG */'设置项更新成功');
        } else {
            return $this->error(/** LANG */'设置项更新失败');
        }
    }

    /**
     * 获取图标icon
     * @return Response
     */
    public function getIcon(): Response
    {
        $ico_tig = [];
        $tig_class = '';
        $ico_defined = [];
        $defined_class = '';

        //官方ico
        $ico_tig_css = 'https://at.alicdn.com/t/c/font_4441878_29ep8jtwh2k.css';
        if (!empty($ico_tig_css) && strpos($ico_tig_css, 'http') === 0 && substr($ico_tig_css, -4) === '.css') {
            $data = cache($ico_tig_css);
            if ($data === null) {
                $content = file_get_contents($ico_tig_css);
                preg_match_all("/" . '\.' . "(.*?)" . '\:before' . "/", $content, $return);
                $ico_tig = $return[1];
                unset($ico_tig[0]);
                preg_match('/font-family:\s*"([^"]+)";/', $content, $matches);
                $tig_class = $matches[1];
                $data['ico_tig'] = $ico_tig;
                $data['tig_class'] = $tig_class;
                cache($ico_tig_css, $data);
            } else {
                $ico_tig = $data['ico_tig'];
                $tig_class = $data['tig_class'];
            }
        }

        // 自定义ico
        $ico_defined_css = ShopConfig::get('ico_defined_css');
        if (!empty($ico_defined_css) && strpos($ico_defined_css, 'http') === 0 && substr($ico_defined_css,
            -4) === '.css') {
            $data = cache($ico_defined_css);
            if ($data === null) {
                $content = file_get_contents($ico_defined_css);
                preg_match_all("/" . '\.' . "(.*?)" . '\:before' . "/", $content, $return);
                $ico_defined = $return[1];
                unset($ico_defined[0]);
                preg_match('/font-family:\s*"([^"]+)";/', $content, $matches);
                $defined_class = $matches[1];
                $data['ico_defined'] = $ico_defined;
                $data['defined_class'] = $defined_class;
                cache($ico_defined_css, $data);
            } else {
                $ico_defined = $data['ico_defined'];
                $defined_class = $data['defined_class'];
            }
        }

        return $this->success([
            'ico_tig' => $ico_tig,
            'tig_class' => $tig_class,
            'ico_defined' => $ico_defined,
            'defined_class' => $defined_class,
        ]);
    }

    /**
     * 发送测试邮件
     * @return Response
     * @throws \exceptions\ApiException
     */
    public function sendTestEmail(): Response
    {
        $email = input("test_mail_address");
        $result = $this->configService->sendTestMail($email);
        return $result ? $this->success(/** LANG */'测试邮件已发送到' . $email) : $this->error(/** LANG */'邮件发送失败，请检查您的邮件服务器设置！');
    }

    /**
     * 上传API文件
     * @return Response
     * @throws \exceptions\ApiException
     */
    public function uploadFile(): Response
    {
        $type = input('type/d');
        $rootPathName = app()->getRootPath() . '/app/common/certs/wechat/';
        $fileName = '';
        if ($type == 1) {
            $fileName = 'apiclient_cert.pem';
        }
        if ($type == 2) {
            $fileName = 'apiclient_key.pem';
        }
        if (empty($fileName)) {
            return $this->error(/** LANG */'未定义文件类型！');
        }
        $fileObj = request()->file('file');
        if (!$fileObj) {
            return $this->error(/** LANG */'未上传文件！');
        }
        $file = new FileStorage($fileObj, 0, $rootPathName, $fileName);
        $file->save();
        return $this->success(/** LANG */'上传成功！');
    }

    /**
     * 生成平台证书
     * @return Response
     */
    public function createPlatformCertificate(): Response
    {
        try {
            app(CertificatesService::class)->getCertificates();
            return $this->success(/** LANG */'操作成功！');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }
}
