<?php
//**---------------------------------------------------------------------+
//**   后台控制器文件 -- 验证
//**---------------------------------------------------------------------+
//**   版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//**   作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//**   提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\api\controller\common;

use app\api\IndexBaseController;
use Fastknife\Exception\ParamException;
use Fastknife\Service\BlockPuzzleCaptchaService;
use Fastknife\Service\ClickWordCaptchaService;
use think\facade\Validate;
use think\Response;

class Verification extends IndexBaseController
{

    public function __construct()
    {
    }

    /**
     * 获取验证码
     * @return Response
     */
    public function captcha(): Response
    {
        try {
            $service = $this->getCaptchaService();
            $data = $service->get();
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
        return $this->success($data);
    }

    /**
     * 一次验证
     * @return Response
     */
    public function check(): Response
    {
        $data = request()->post();
        try {
            $this->validateData($data);
            $service = $this->getCaptchaService();
            $service->check($data['token'], $data['pointJson']);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
        return $this->success($data);
    }

    /**
     * 二次验证
     * @return Response
     */
    public function verification(): Response
    {
        $data = request()->post();
        try {
            $this->validateData($data);
            $service = $this->getCaptchaService();
            $service->verification($data['token'], $data['pointJson']);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
        return $this->success([]);
    }

    /**
     * 获取验证码服务
     * @return BlockPuzzleCaptchaService|ClickWordCaptchaService
     */
    protected function getCaptchaService(): BlockPuzzleCaptchaService | ClickWordCaptchaService
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

    /**
     * 验证数据
     * @param $data
     * @throws ParamException
     */
    protected function validateData($data): void
    {
        $rules = [
            'token' => ['require'],
            'pointJson' => ['require'],
        ];
        $validate = Validate::rule($rules)->failException(true);
        $validate->check($data);
    }
}
