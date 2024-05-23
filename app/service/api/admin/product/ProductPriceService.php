<?php

namespace app\service\api\admin\product;

use app\service\api\admin\BaseService;
use app\service\api\admin\promotion\SeckillService;

class ProductPriceService extends BaseService
{
    /**
     * 获取商品最终价格
     * @param int $product_id
     * @param float $price
     * @param int $sku_id
     * @param int $user_rank_id
     * @param array $ranks_list
     * @return float|int
     * @throws \exceptions\ApiException
     */
    public function getProductFinalPrice(int $product_id, float $price, int $sku_id, int $user_rank_id = 0, array $ranks_list = []): float|int
    {
        //1.促销价格优先级最高，促销价格存在时属性价格，阶梯价格，商品会员价格，会员等级价格无效
        $seckill = app(SeckillService::class)->getProductActivityInfo($product_id, $sku_id);
        if ($seckill) {
            if ($seckill['seckill_sales'] < $seckill['seckill_stock'] && !empty($seckill['seckill_stock']))
                return $seckill['seckill_price'];
        }

        //2.获取商品会员价格表(必须在没有促销价格，属性价格的前提下)
//        $member_price_list = app(ProductMemberPriceService::class)->getMemberPriceList($product_id);
//        if (isset($member_price_list[$user_rank_id]) && $member_price_list[$user_rank_id] > 0 && empty($sku_data)) {
//            return $member_price_list[$user_rank_id];
//        }

        //3.属性价格存在时阶梯价格无效（此时商品会员价格优先级高级会员等级价格）
//        $sku_data = app(ProductSkuService::class)->getDetail($sku_id);
//        if (!empty($sku_data)) {
//            if (isset($sku_data['sku_price']) && $sku_data['sku_price'] > 0) $price = $sku_data['sku_price'];
//        }
        //4.会员等级价格
        foreach ($ranks_list as $key => $value) {
            if ($value['rank_id'] == $user_rank_id) {
                $discount = floatval($value['discount']);
                $price = round($price * $discount / 100, 2);
            }
        }

        return $price;
    }
}