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

namespace app\index\controller\common;

use app\common\utils\Config as UtilsConfig;
use app\index\IndexBaseController;
use app\service\api\admin\image\Image;
use think\App;
use think\Response;

/**
 * 首页控制器
 */
class Config extends IndexBaseController
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
     * 首页
     *
     * @return Response
     */
    public function base(): Response
    {

        return $this->success([
            'theme_id' => UtilsConfig::get('theme_id', 'theme_style'),
            'theme_style' => UtilsConfig::getConfig('theme_style'),
            'shop_name' => UtilsConfig::get('shop_name'),
            'shop_title' => UtilsConfig::get('shop_title'),
            'shop_title_suffix' => UtilsConfig::get('shop_title_suffix'),
            'shop_logo' => UtilsConfig::get('shop_logo'),
            'shop_keywords' => UtilsConfig::get('shop_keywords'),
            'shop_desc' => UtilsConfig::get('shop_desc'),
            'storage_url' => app(Image::class)->getStorageUrl(),
            'dollar_sign' => UtilsConfig::get('dollar_sign') ?? '¥',
            'dollar_sign_cn' => UtilsConfig::get('dollar_sign_cn') ?? '元',
        ]);
    }

    /**
     * 售后服务配置
     * @return Response
     */
    public function afterSalesService(): Response
    {
        return $this->success([
            'item' => UtilsConfig::getConfig('after_sales_service'),
        ]);
    }

}
