<?php
//**---------------------------------------------------------------------+
//** 通用接口控制器文件 -- 通用
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\api\controller\common;

use app\api\IndexBaseController;
use think\App;
use think\Response;
use chillerlan\QRCode\{QRCode, QROptions};

/**
 * 工具组件
 */
class Util extends IndexBaseController
{
    /**
     * 构造函数
     *
     * @param App $app
     */
    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    /**
     * 二维码
     *
     * @return Response
     */
    public function qrCode(): Response
    {
        $data = input('url');
        //$data   = 'otpauth://totp/test?secret=B3JX4VCVJDVNXNZ5&issuer=chillerlan.net';
        $qrcode = (new QRCode)->render($data);
        return $this->success([
            'qrcode' => $qrcode,
        ]);
    }

}
