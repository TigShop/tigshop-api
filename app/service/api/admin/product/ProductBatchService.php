<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 商品批量处理
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\product;

use app\common\exceptions\ApiException;
use app\common\utils\Config;
use app\common\utils\Excel;
use app\common\utils\Time;
use app\model\category\Category;
use app\model\product\Brand;
use app\model\product\Product;
use app\service\api\admin\BaseService;
use think\Exception;
use think\facade\Db;

class ProductBatchService extends BaseService
{
    /**
     * 商品批量导出
     * @param array $data
     * @return bool
     */
    public function productBatchDeal(array $data): bool
    {
        if (empty($data["range_ids"]) && $data["deal_range"] != 0) {
            throw new \Exception("请选择批量处理范围");
        }
        switch ($data["deal_range"]) {
            case 1:
                // 分类
                $cate_ids = [];
                foreach ($data["range_ids"] as $k => $cate_id) {
                    $cate_ids[] = app(CategoryService::class)->catAllChildIds($cate_id);
                }
                $cate_ids = array_unique(array_filter(array_merge(...$cate_ids)));
                $query = Product::where("is_delete", 0)->whereIn("category_id", $cate_ids);
                break;
            case 2:
                // 品牌
                $query = Product::where(["brand_id" => $data["range_ids"], "is_delete" => 0]);
                break;
            case 3:
                // 商品
                $query = Product::where("is_delete", 0)->whereIn("product_id", $data["range_ids"]);
                break;
            default:
                // 全部商品
                $query = Product::where("is_delete", 0);
                break;
        }

        // 商品批量导出
        $data = $query->with(["brand"])->field("product_name,product_sn,category_id,product_price,market_price,product_status,brand_id,pic_thumb,keywords,product_brief,product_desc,product_weight,product_stock")
            ->append(["category_tree_name"])->select()->toArray();
        $result = $this->exportProductData($data);
        // 标题
        $expore_title = ["商品名称", "商品编号", "分类", "商品售价", "市场价", "是否上架", "品牌", "商品相册", "关键词", "商品描述", "详细描述", "商品重量(KG)", "库存"];
        // 文件名
        $file_name = Config::get("shop_name") . "商城批量导出商品" . Time::getCurrentDatetime("Ymd") . rand(1000, 9999);
        Excel::export($expore_title, $file_name, $result);
        return true;
    }

    /**
     * 商品批量修改
     * @param array $data
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function productBatchEdit(array $data): array
    {
        $count = 0;
        $list = [
            "product_name",
            "category_id",
            "brand_id",
            "product_sn",
            "product_tsn",
            "product_price",
            "market_price",
            "shipping_tpl_id",
            "free_shipping",
            "is_new",
            "is_best",
            "is_hot",
            "sort_order",
            "product_id",
            "product_status",
        ];
        try {
            Db::startTrans();
            if (!empty($data)) {
                foreach ($data as $k => $row) {
                    foreach ($row as $k1 => $v1) {
                        if (!in_array($k1, $list)) {
                            throw new \Exception("LINE {$index} 错误：不支持的字段" . $k1);
                        }
                    }
                    $index = $k + 1;
                    // 修改
                    if (isset($row['product_sn'])) {
                        if (empty($row['product_sn'])) {
                            throw new \Exception("LINE {$index} 错误：商品编号不能为空");
                        }
                        $product_id = Product::where([
                            "product_sn" => $row['product_sn'],
                            "is_delete" => 0,
                        ])->value("product_id");
                        if (!$product_id && $product_id != $row['product_id']) {
                            throw new \Exception("LINE {$index} 错误：商品编号重复");
                        }
                    }
                    if (isset($row['product_name']) && empty($row['product_name'])) {
                        throw new Exception("LINE {$index} 错误：商品名称不能为空");
                    }

                    Product::where("product_id", $row['product_id'])->save($row);
                    $count++;
                }
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw new ApiException($e->getMessage());
        }
        return ["count" => $count];
    }

    /**
     * 商品批量上传 / 修改
     * @param array $data
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function productBatchModify(array $data): array
    {
        $file_path = !empty($_FILES['file']['tmp_name']) ? $_FILES['file']['tmp_name'] : "";
        $file_row = Excel::import($file_path);
        $count = 0;
        $msg = "";
        $result = [];
        if (!empty($file_row)) {
            foreach ($file_row as $k => $row) {
                $index = $k + 1;
                if (empty($row[0]) || empty($row[2])) {
                    $msg .= "LINE {$index} 存在商品名称为空或分类为空的数据，已忽略此数据";
                    continue;
                }

                if ($data["is_upload"]) {
                    // 上传
                    if (empty($row[1])) {
                        // 商品编号为空则自动生成
                        $row[1] = app(ProductService::class)->creatNewProductSn();
                    } else {
                        // 唯一验证
                        if (Product::where("product_sn", $row[1])->count()) {
                            $msg .= "LINE {$index} 错误：存在商品编号重复的数据，已忽略此数据";
                            continue;
                        }
                    }
                } else {
                    // 修改
                    $product_id = Product::where(["product_sn" => $row[1], "is_delete" => 0])->value("product_id");
                    if (!$product_id) {
                        $msg .= "LINE {$index} 错误：商品编号不存在或已删除";
                        continue;
                    }
                }

                $row[2] = explode("|", trim($row[2]));
                $cat_id = $this->getCategoryIds($row[2], $data["is_auto_cat"]);
                if (empty($cat_id)) {
                    $msg .= "LINE {$index} 错误：存在分类不存在的数据，已忽略此数据";
                    continue;
                }

                // 品牌
                if (!empty($row[6])) {
                    $brand_id = $this->getBrandId($row[6], $data["is_auto_brand"]);
                }

                $product_price = $row[3] ?? "0.00";
                $res = [
                    "product_name" => $row[0],
                    "product_sn" => $row[1],
                    "category_id" => $cat_id,
                    "product_price" => $product_price,
                    "market_price" => $row[4] ?? Config::get("market_price_rate") * $product_price,
                    "product_status" => !empty($row[5]) ? 1 : 0,
                    "brand_id" => $brand_id ?? 0,
                    "pic_thumb" => $row[7] ?? "",
                    "keywords" => $row[8] ?? "",
                    "product_brief" => $row[9] ?? "",
                    "product_desc" => $row[10] ?? "",
                    "product_weight" => $row[11] ?? 0,
                    "product_stock" => $row[12] ?? 0,
                    "store_id" => (request()->storeId) > 0 ? request()->storeId : 0,
                ];
                $result[] = $res;

                if ($data["is_upload"] == 0) {
                    unset($res["product_sn"]);
                    if (!$data["is_change_pic"]) {
                        unset($res["pic_thumb"]);
                    }
                    Product::where("product_id", $product_id)->save($res);
                }
                $count++;
            }
            if ($data["is_upload"]) {
                // 上传
                Product::saveAll($result);
            }
            return [
                "count" => $count,
                "msg" => $msg,
            ];
        }
        return ["count" => 0, "msg" => "请上传有数据的文件"];
    }

    /**
     * 是否自动添加分类
     * @param array $cate_name
     * @param int $is_auto_cat
     * @return int
     */
    public function getCategoryIds(array $cate_name, int $is_auto_cat): int
    {
        $cat_id = $parent_id = 0;
        foreach ($cate_name as $k => $name) {
            $cat_id = Category::where("category_name", $name)->value("category_id");
            if (!$cat_id && $is_auto_cat) {
                // 添加分类
                $result = Category::create(["category_name" => $name, "parent_id" => $parent_id]);
                $cat_id = $result["category_id"];
                $parent_id = $cat_id;
            }
        }
        return $cat_id;
    }

    /**
     * 是否自动添加品牌
     * @param string $brand_name
     * @param int $is_auto_brand
     * @return int
     */
    public function getBrandId(string $brand_name, int $is_auto_brand): int
    {
        $brand_id = 0;
        if (!empty($brand_name)) {
            $brand_id = Brand::where("brand_name", $brand_name)->value("brand_id");
            if (!$brand_id && $is_auto_brand) {
                // 添加品牌
                $result = Brand::create(["brand_name" => $brand_name]);
                $brand_id = $result["brand_id"];
            }
        }
        return $brand_id;
    }

    /**
     * 下载模版文件
     * @return bool
     */
    public function downloadTemplate(): bool
    {
        $file_name = Config::get("shop_name") . "商城商品导入模板文件";
        $title = [
            '商品名称（必须）',
            '商品编号（为空则自动生成）',
            '分类（必须）',
            '商品售价',
            '市场价（为空则自动生成）',
            '是否上架（默认1）',
            '品牌',
            '商品相册',
            '关键词',
            '商品描述',
            '详细描述',
            '重量（kg）',
            '库存（默认0）',
        ];
        $data[] = [
            'Xiaomi/小米 小米电视4A 55英寸4k高清智能网络平板液晶电视40 49',
            'SN00011',
            '家用电器|电视|平板电视',
            '2000',
            '2200',
            '1',
            '小米',
            'img/item/202009/1599117139VhqZQYsACs5awn4N6S!!pic.jpeg',
            '电视 小米 4k',
            '官方授权 全国联保 现货销售',
            '<p><img src="https://img.alicdn.com/imgextra/i3/2656222132/TB276U2j0RopuFjSZFtXXcanpXa_%21%212656222132.jpg" class="img-ks-lazyload" align="absmiddle"><img src="https://img.alicdn.com/imgextra/i4/2656222132/TB25FSAjr8kpuFjy0FcXXaUhpXa_%21%212656222132.jpg" class="img-ks-lazyload" align="absmiddle"><img src="https://img.alicdn.com/imgextra/i1/2656222132/TB2DE.3jZtnpuFjSZFvXXbcTpXa_%21%212656222132.jpg" class="img-ks-lazyload" align="absmiddle"></p>',
            '10',
            '9999',
        ];
        Excel::export($title, $file_name, $data);
        return true;
    }

    /**
     * 导出商品数据
     * @param array $data
     * @return array
     */
    public function exportProductData(array $data): array
    {
        if (empty($data)) {
            return [];
        }
        foreach ($data as $k => $v) {
            if (isset($v["category_id"])) {
                $v["category_id"] = $v["category_tree_name"];
                unset($v["category_tree_name"]);
            }
            if (isset($v["brand_id"])) {
                $v["brand_id"] = $v["brand_name"];
                unset($v["brand_name"]);
            }
            $result[] = array_values($v);
        }
        return $result;
    }

    /**
     * 获取分页信息
     *
     * @param int $count
     * @param int $size
     * @param int $page
     * @return array
     */
    public function getPageInfo(int $count, int $size = 15, int $page = 1): array
    {
        if ($count > $size) {
            $total_page = $count % $size != 0 ? intval($count / $size) + 1 : intval($count / $size);
        } else {
            $total_page = 1;
        }
        $page_info = [
            "total_page" => $total_page,
            "count" => $count,
            "size" => $size,
            "page" => $page,
        ];
        return $page_info;
    }
}
