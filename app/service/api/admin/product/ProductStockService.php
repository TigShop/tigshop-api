<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 商品库存相关
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\product;

use app\model\product\Product;
use app\model\product\ProductSku;
use app\service\api\admin\BaseService;
use app\service\api\admin\promotion\SeckillService;

/**
 * 商品库存相关服务类
 */
class ProductStockService extends BaseService
{

    public function __construct()
    {
    }

    /**
     * 获取商品实际库存
     * @param int $product_id
     * @param int $sku_id
     * @return int
     */
    public function getProductStock(int $product_id, int $sku_id): int
    {
        //秒杀库存
        $seckill = app(SeckillService::class)->getProductActivityInfo($product_id, $sku_id);
        if ($seckill) {
            return $seckill['seckill_stock'];
        }

        if ($sku_id == 0) {
            $result = Product::where('product_id', $product_id)->value('product_stock');
        } else {
            $result = ProductSku::where(['product_id' => $product_id, 'sku_id' => $sku_id])->value('sku_stock');
        }

        return $result ?: 0;
    }

    /**
     * 检查商品的库存是否充足
     * @param int $quantity
     * @param int $product_id
     * @param int $sku_id
     * @return bool
     */
    public function checkProductStock(int $quantity, int $product_id, int $sku_id = 0): bool
    {
        $product_stock = $this->getProductStock($product_id, $sku_id);
        return $quantity <= $product_stock;
    }
}
