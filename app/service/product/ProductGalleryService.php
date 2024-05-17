<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 商品相册
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\product;

use app\model\product\Product;
use app\model\product\ProductGallery;
use app\service\BaseService;
use app\service\image\Image;

/**
 * 商品相册服务类
 */
class ProductGalleryService extends BaseService
{

    public function __construct()
    {
    }

    /**
     * 获取商品相册
     *
     * @param array $filter
     * @return array
     */
    public function getProductGalleryList(int $product_id): array
    {
        $result = ProductGallery::where('product_id', $product_id)->order('sort_order ASC')->select();
        if (request()->adminUid == 0) {
            // 前台接口需要隐藏源图
            $result = $result->hidden(['pic_original']);
        }
        return $result->toArray();
    }

    /**
     * 更新商品相册
     * @param int $product_id
     * @param array $img_list
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function updateProductGallery(int $product_id, array $img_list = []): bool
    {
        //删除记录
        if (empty($product_id)) {
            return false;
        }

        if (empty($img_list)) {
            $update_data = ['pic_url' => '', 'pic_thumb' => '', 'pic_original' => ''];
            Product::where('product_id', $product_id)->save($update_data);
        }
        $pic_ids = [];
        foreach ($img_list as $key => $value) {
            if (isset($value['product_id']) && $value['product_id'] > 0 && $value['pic_id'] > 0) {
                $update_data = ['sort_order' => $key + 1, 'pic_desc' => $value['pic_desc']];
                ProductGallery::where('pic_id', $value['pic_id'])->save($update_data);
                //首图处理
                if ($key == 0) {
                    $gallery = ProductGallery::where('pic_id', $value['pic_id'])->field('pic_url,pic_thumb,pic_original')->find();
                    if ($gallery) {
                        $update_data = ['pic_url' => $gallery['pic_url'], 'pic_thumb' => $gallery['pic_thumb'], 'pic_original' => $gallery['pic_original']];
                        Product::where('product_id', $product_id)->save($update_data);
                    }
                }
                array_push($pic_ids, $value['pic_id']);
            } else {
                $image = new Image($value['pic_url']);
                $pic_original = $image->save();
                $pic_url = $image->makeThumb(400, 400);
                $pic_large = $image->makeThumb(800, 800);
                $pic_thumb = $image->makeThumb(200, 200);
                $update_data = [
                    'pic_url' => $pic_url,
                    'pic_thumb' => $pic_thumb,
                    'pic_large' => $pic_large,
                    'pic_original' => $pic_original,
                    'product_id' => $product_id,
                    'pic_desc' => $value['pic_desc'] ?? '',
                    'sort_order' => $key + 1,
                ];
                $pic_id = ProductGallery::insertGetId($update_data);
                array_push($pic_ids, $pic_id);
                if ($key == 0) {
                    $update_data = ['pic_url' => $pic_url, 'pic_thumb' => $pic_thumb, 'pic_original' => $pic_original];
                    Product::where('product_id', $product_id)->save($update_data);
                }
            }
        }
        // 删除被删除了的相册
        ProductGallery::where([['product_id', '=', $product_id], ['pic_id', 'NOT IN', $pic_ids]])->delete();
        return true;
    }
}
