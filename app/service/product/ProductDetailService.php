<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 商品
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\product;

use app\common\exceptions\ApiException;
use app\common\utils\Time;
use app\model\product\Product;
use app\model\product\ProductServices;
use app\model\promotion\Seckill;
use app\model\promotion\SeckillItem;
use app\model\user\CollectProduct;
use app\service\BaseService;
use app\service\promotion\SeckillService;
use app\service\user\FeedbackService;

/**
 * 商品服务类
 */
class ProductDetailService extends BaseService
{
    protected int|string $id;
    public $product = null;

    public function __construct(int | string $id)
    {
        $this->id = $id;
    }

    /**
     * 获取详情
     * @return array
     * @throws ApiException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getDetail(): array
    {
        if ($this->product === null) {
            $result = Product::find($this->id);

            if (!$result) {
                throw new ApiException('商品不存在');
            }

            // 判断商品是否参与秒杀
            $seckill = Seckill::where("product_id", $this->id)
                ->where("seckill_start_time", "<=", Time::now())
                ->where("seckill_end_time", ">=", Time::now())
                ->count();
            if ($seckill) {
                $result->is_seckill = 1;
            } else {
                $result->is_seckill = 0;
            }
            $this->product = $result;
        }

        $item = $this->product->toArray();
        return $item;
    }

    /**
     * 设置商品对象
     * @param object $product
     * @return void
     */
    public function setDetail(object $product): void
    {
        $this->product = $product;
    }

    /**
     * 获取商品秒杀信息
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getSeckillInfo(): array
    {
        $seckill = Seckill::with(["seckill_item"])
            ->where("product_id", $this->id)
            ->where("seckill_start_time", "<=", Time::now())
            ->where("seckill_end_time", ">=", Time::now())
            ->select()->toArray();
        return $seckill;
    }

    /**
     * 获取默认选择的属性
     * @param int $sku_id
     * @return array
     * @throws ApiException
     */
    public function getSelectValue(int $sku_id): array
    {
        if (empty($sku_id)) return [];
        $sku_info = app(ProductSkuService::class)->getDetail($sku_id);
        if (empty($sku_info)) return [];
        if (empty($sku_info['sku_data'])) return [];
        $select_value = [];
        foreach ($sku_info['sku_data'] as $sku) {
            $select_value[] = $sku['name'] . ':' . $sku['value'];
        }
        return $select_value;
    }

    /**
     * 获取商品图文详情
     * @return array
     */
    public function getDescArr(): array
    {
        return app(ProductService::class)->getProductDescArr($this->product->product_desc);
    }

    /**
     * 获取相册列表
     * @return array
     */
    public function getPicList(): array
    {
        return app(ProductGalleryService::class)->getProductGalleryList($this->id);
    }

    /**
     * 获取属性列表
     * @return array
     */
    public function getAttrList(): array
    {
        return app(ProductAttributesService::class)->getAttrList($this->id);
    }

    /**
     * 获取sku列表
     * @return array
     */
    public function getSkuList(): array
    {
        return app(ProductSkuService::class)->getSkuList($this->id);
    }

    /**
     * 获取商品评论评分详情（随商品加载）
     * @return array
     */
    public function getProductCommentRankDetail(): array
    {
        return app(CommentService::class)->getProductCommentRankDetail($this->id);
    }

    /**
     * 获取商品评论详情（完整）
     * @return array
     */
    public function getProductCommentDetail(): array
    {
        return app(CommentService::class)->getProductCommentDetail($this->id);
    }

    /**
     * 获取商品评论列表
     * @param array $params
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getProductCommentList(array $params): array
    {
        $params['product_id'] = $this->id;
        return app(CommentService::class)->getProductCommentList($params);
    }

    /**
     * 获取商品评论数量
     * @param array $params
     * @return int
     * @throws \think\db\exception\DbException
     */
    public function getProductCommentCount(array $params): int
    {
        $params['product_id'] = $this->id;
        return app(CommentService::class)->getProductCommentCount($params);
    }

    /**
     * 获取商品Sku详情
     * @param int $sku_id
     * @return array
     * @throws ApiException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getProductSkuDetail(int $sku_id = 0): array
    {
        $product = $this->getDetail();
        $stock = $product['product_stock'] ? $product['product_stock'] : 0;
        $price = $product['product_price'] ? $product['product_price'] : 0;
        $is_seckill = 0;
        $seckill_end_time = 0;
        // 判断是否有sku
        if ($sku_id > 0) {
            $sku = app(ProductSkuService::class)->getDetail($sku_id);
            if ($sku) {
                $id = $sku['sku_id'];
                $stock = $sku['sku_stock'];
                $price = $sku['sku_price'] > 0 ? $sku['sku_price'] : $price;
                $data = $sku['sku_data'];
            }
        }
        //判断是否有促销
        $seckill = app(SeckillService::class)->getProductActivityInfo($product['product_id'], $sku_id);
        if ($seckill) {
            $price = $seckill['seckill_price'];
            if ($seckill['seckill_stock'] > 0) {
                $stock = $seckill['seckill_stock'] - $seckill['seckill_sales'];
            }
            $is_seckill = 1;
            $seckill_end_time = Time::format($seckill['seckill_end_time']);
        }
        return [
            'id' => $id ?? 0,
            'data' => $data ?? [],
            'price' => $price,
            'stock' => max($stock, 0),
            'is_seckill' => $is_seckill,
            'seckill_end_time' => $seckill_end_time,
        ];
    }

    /**
     * 获取商品服务信息
     * @return array
     * @throws ApiException
     */
    public function getServiceList(): array
    {
        $product = Product::where("product_id|product_sn", $this->id)->find();
        if (!$product) {
            throw new ApiException("商品不存在");
        }
        $result = ProductServices::whereIn("product_service_id", $product->product_service_ids)
            ->where("default_on", 1)->order("sort_order", "asc")
            ->column("product_service_id,product_service_name,ico_img");
        return $result;
    }

    /**
     * 判断是否被收藏
     * @return bool
     * @throws ApiException
     */
    public function getIsCollect(): bool
    {
        $product = Product::where("product_id|product_sn", $this->id)->find();
        if (!$product) {
            throw new ApiException("商品不存在");
        }
        if (CollectProduct::where(["product_id" => $product->product_id, "user_id" => request()->userId])->count()) {
            return true;
        }
        return false;
    }

    /**
     * 获取商品咨询数量
     * @return int
     * @throws \think\db\exception\DbException
     */
    public function getConsultationCount(): int
    {
        return app(FeedbackService::class)->getProductFeedbackCount($this->id);
    }
}
