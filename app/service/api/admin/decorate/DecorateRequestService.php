<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 装修
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\decorate;

use app\service\api\admin\BaseService;
use app\service\api\admin\product\ProductService;
use exceptions\ApiException;

/**
 * 装修服务类
 */
class DecorateRequestService extends BaseService
{

    public function __construct()
    {
    }
    public function getProductList(array $params = []): array
    {
        $filter = [];

        $params['product_number'] = $params['product_number'] <= 0 ? 3 : $params['product_number'];
        $params['product_number'] = $params['product_number'] > 50 ? 50 : $params['product_number'];
        $filter['size'] = intval($params['product_number']);

        $filter['page'] = isset($filter['page']) && $filter['page'] > 0 ? intval($filter['page']) : 1;
        if ($params['product_select_type'] == 1) {
            $filter['product_ids'] = array_map('intval', $params['product_ids']);
        } elseif ($params['product_select_type'] == 2) {
            $filter['category_id'] = intval($params['product_category_id']);
        } elseif ($params['product_select_type'] == 3) {
            $filter['intro_type'] = $params['product_tag'];
        } else {
            throw new ApiException('#type错误');
        }
        $result = app(ProductService::class)->getProductList($filter);
        return $result;
    }

}
