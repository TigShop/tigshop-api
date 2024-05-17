<?php
//**---------------------------------------------------------------------+
//** 通用接口控制器文件 -- 地区
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\index\controller\sys;

use app\index\IndexBaseController;
use app\service\setting\RegionService;
use think\App;
use think\Response;

/**
 * 地区控制器
 */
class Region extends IndexBaseController
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
     * 获取地区
     *
     * @return Response
     */
    public function getRegion(): Response
    {
        $region_ids = input('region_ids', '');
        $region_ids = explode(',', $region_ids);
        $region_list = app(RegionService::class)->getRegionByIds($region_ids);
        return $this->success([
            'region_list' => $region_list,
        ]);
    }

    /**
     * 获得所有省份接口
     * @return Response
     */
    public function getProvinceList(): Response
    {
        $region_list = app(RegionService::class)->getProvinceList();
        foreach ($region_list as $key => $value) {
            $region_list[$key]['region_name'] = str_replace(['省', '市', '自治区'], '', $value['region_name']);
        }
        return $this->success([
            'region_list' => $region_list,
        ]);
    }

    /**
     * 获得用户所在省份
     * @return Response
     */
    public function getUserRegion(): Response
    {
        $ip2region = new \Ip2Region();
        $result = $ip2region->simple(request()->ip()); //@todo 因内网操作后续改为真实ip
        $province = mb_substr($result, 2, 2);
        $region_list = app(RegionService::class)->getProvinceList();
        $user_region = '';
        foreach ($region_list as $region) {
            if (strpos($region['region_name'], $province) !== false) {
                $user_region = $region;
                break;
            }
        }
        if (empty($user_region)) {
            $user_region = $region_list['0'];
        }
        return $this->success([
            'region' => $user_region,
        ]);
    }

}
