<?php

namespace app\service\api\admin\captcha;

use app\service\api\admin\BaseService;
use exceptions\ApiException;
use Fastknife\Exception\ParamException;
use Fastknife\Service\BlockPuzzleCaptchaService;
use Fastknife\Service\ClickWordCaptchaService;
use think\facade\Cache;

/**
 * 验证码服务类
 */
class CaptchaService extends BaseService
{

    public function __construct()
    {
    }
    // range取值范围
    protected $range = ['', 'admin_login', 'login', 'mobile'];

    protected string $tag;
    protected string $token;
    protected int $allowNoCheckTimes = 0;
    /**
     * 设置标识符
     *
     * @param string $tag
     * @return self
     */
    public function setTag(string $tag = ''): self
    {
        $this->tag = $tag;
        return $this;
    }
    /**
     * 设置需要验证的token
     *
     * @param string $token
     * @return self
     */
    public function setToken(string $token = ''): self
    {
        $this->token = $token;
        return $this;
    }
    /**
     * 设置多少次内输入可以免验证，为0或不设置则表示必须验证
     *
     * @param integer $times
     * @return self
     */
    public function setAllowNoCheckTimes(int $times = 3): self
    {
        $this->allowNoCheckTimes = $times;
        return $this;
    }

    // 添加错误次数
    public function addAccessTimes()
    {
        $times = Cache::get('accessTimes:' . $this->tag);
        $times = !empty($times) ? $times + 1 : 1;
        Cache::set('accessTimes:' . $this->tag, $times, 120);
    }
    // 验证可不需要验证的最多次数，大于则
    public function isNeedCheck(): bool
    {
        $times = Cache::get('accessTimes:' . $this->tag);
        $times = !empty($times) ? intval($times) : 1;
        return $times <= $this->allowNoCheckTimes;
    }
    // 滑块验证码二次验证
    public function verification()
    {
        //每调用一次增加一次验证次数
        $this->addAccessTimes();
        if ($this->isNeedCheck() == false) {
            // 错误次数过多需要验证(默认3次)
            if (!$this->token) {
                throw new ApiException('需要行为验证！', 1002);
            } else {
                try {
                    $service = $this->getCaptchaService();
                    $service->verificationByEncryptCode($this->token);
                } catch (\Exception $e) {
                    throw new ApiException('行为验证错误！', 1002);
                }
            }
        }
        return true;
    }

    protected function getCaptchaService()
    {
        $captchaType = 'blockPuzzle'; //request()->post('captchaType', null);
        $config = config('verification');
        switch ($captchaType) {
            case "clickWord":
                $service = new ClickWordCaptchaService($config);
                break;
            case "blockPuzzle":
                $service = new BlockPuzzleCaptchaService($config);
                break;
            default:
                throw new ParamException('captchaType参数不正确！');
        }
        return $service;
    }
    public function creatTpCaptcha($range = '')
    {
        if (!in_array($range, $this->range)) {
            return false;
        }

        $uuid = md5(uniqid(md5(microtime(true)), true));
        $res = \think\captcha\facade\Captcha::create();
        $base64_image = "data:image/png;base64," . base64_encode($res->getData());
        $key = session('captcha.key');
        session('captcha', null);
        Cache::set('captcha:' . $range . ':' . $uuid, $key, 60);
        return [
            'data' => $base64_image,
            'uuid' => $uuid,
        ];
    }
    public function checkTpCaptcha($range = '', $uuid = null, $code = null)
    {
        if ($uuid === null) {
            $uuid = input('captcha_uid');
        }
        if ($code === null) {
            $code = input('captcha_code');
        }
        if (!$code || !$uuid) {
            return false;
        }

        $key = Cache::pull('captcha:' . $range . ':' . $uuid); //pull为获取并删除
        if (!$key) {
            return false;
        }
        $code = mb_strtolower($code, 'UTF-8');
        $res = password_verify($code, $key);
        return $res ? true : false;
    }
}
