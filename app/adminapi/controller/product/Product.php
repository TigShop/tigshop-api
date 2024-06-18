<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 商品管理
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\adminapi\controller\product;

use app\adminapi\AdminBaseController;
use app\model\product\ProductArticle;
use app\service\api\admin\authority\SuppliersService;
use app\service\api\admin\participle\ParticipleService;
use app\service\api\admin\product\ProductAttributesService;
use app\service\api\admin\product\ProductAttributesTplService;
use app\service\api\admin\product\ProductGalleryService;
use app\service\api\admin\product\ProductMemberPriceService;
use app\service\api\admin\product\ProductService;
use app\service\api\admin\product\ProductServicesService;
use app\service\api\admin\product\ProductSkuService;
use app\service\api\admin\shipping\ShippingTplService;
use app\service\api\admin\user\UserRankService;
use app\validate\product\ProductValidate;
use think\App;
use think\facade\Db;
use utils\Config;
use utils\Time;

/**
 * 商品管理控制器
 */
class Product extends AdminBaseController
{
    protected ProductService $productService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param ProductService $productService
     */
    public function __construct(App $app, ProductService $productService)
    {
        parent::__construct($app);
        $this->productService = $productService;
    }

    /**
     * 列表页面
     *
     * @return \think\Response
     */
    public function list(): \think\Response
    {
        $filter = $this->request->only([
            'is_show/d' => -1,
            'keyword' => '',
            'page/d' => 1,
            'size/d' => 15,
            'sort_field' => 'product_id',
            'sort_order' => 'desc',
            'product_id/d' => 0,
            'is_delete/d' => -1,
            'category_id/d' => 0,
            'brand_id/d' => 0,
            'ids' => null,
            'shop_id/d' => -2, // 店铺id
            'intro_type' => '', // 商品类型
            'product_status/d' => -1, // 上下架状态
            'check_status/d' => -1, // 审核状态
        ], 'get');

        if(request()->adminType == 'shop')
        {
            $filter['shop_id'] = request()->shopId;
        }
        $filterResult = $this->productService->getFilterResult($filter);
        $total = $this->productService->getFilterCount($filter);
        $waiting_checked_count = $this->productService->getWaitingCheckedCount();

        return $this->success([
            'filter_result' => $filterResult,
            'filter' => $filter,
            'total' => $total,
            'waiting_checked_count' => $waiting_checked_count,
        ]);
    }

    /**
     * 配置型
     * @return \think\Response
     */
    public function config(): \think\Response
    {
        //获取商品类型存在规格的类型
        $attr_tpl_list = app(ProductAttributesTplService::class)->getAttrTplList();
        //运费模板
        $shipping_tpl_list = app(ShippingTplService::class)->getShippingTplList();
        // 供应商
        $suppliers_list = app(SuppliersService::class)->getSuppliersList();
        // 服务说明
        $service_list = app(ProductServicesService::class)->getProductService();
        $item = [];
        $item['user_rank_list'] = app(UserRankService::class)->getUserRankList();

        foreach ($item['user_rank_list'] as $key => $value) {
            if (isset($member_price_list[$value['rank_id']]) && !empty($member_price_list[$value['rank_id']])) {
                $item['user_rank_list'][$key]['price'] = $member_price_list[$value['rank_id']];
            } else {
                $item['user_rank_list'][$key]['price'] = -1;
            }
        }
        if (request()->shopId > 0) {
            if (Config::get('store_product_need_check') == 1) {
                $item['check_status'] = 0;
                $item['product_status'] = 0;
            } else {
                $item['check_status'] = 1;
                $item['product_status'] = 1;
            }
            $item['shop_id'] = request()->shopId;
        }
        $item['product_service_ids'] = Db::name('product_services')->where('default_on',
            1)->column('product_service_id');
        return $this->success([
            'shipping_tpl_list' => $shipping_tpl_list,
            'suppliers_list' => $suppliers_list,
            'service_list' => $service_list,
            'attr_tpl_list' => $attr_tpl_list,
            'item' => $item
        ]);
    }

    /**
     *详情数据
     * @return \think\Response
     */
    public function detail(): \think\Response
    {
        $id = input('id/d', 0);
        $item = $this->productService->getDetail($id);

        if ($item['check_status'] != 1) {
            $item['product_status'] = 0;
        }
        /* 根据商品重量的单位重新计算 */
        if ($item['product_weight'] > 0) {
            $item['product_weight_by_unit'] = ($item['product_weight'] >= 1) ? $item['product_weight'] : ($item['product_weight'] / 0.001);
        }
        // 关联文章
        $item["product_article_list"] = ProductArticle::where("goods_id", $id)->column('article_id');

        //属性
        $item['attr_list'] = app(ProductAttributesService::class)->getAttrList($item['product_id']);
        //规格
        $item['product_list'] = app(ProductSkuService::class)->getSkuList($item['product_id']);
        return $this->success([
            'item' => $item,
        ]);
    }

    /**
     * 商品复制
     * @return \think\Response
     */
    public function copy(): \think\Response
    {
        $id = input('product_id/d', 0);
        $item = $this->productService->getDetail($id);
        // 关联文章
        $item["product_article_list"] = ProductArticle::where("goods_id", $id)->column('article_id');

        //属性
        $item['attr_list'] = app(ProductAttributesService::class)->getAttrList($item['product_id']);
        //规格
        $item['product_list'] = app(ProductSkuService::class)->getSkuList($item['product_id']);
        unset($item['product_id']);
        $result = $this->productService->updateProduct($id, $item, true);
        if ($result) {
            $id = $result;
            /* 处理属性 */
            app(ProductAttributesService::class)->dealProductAttr($id, $item['attr_list']);
            /* 处理规格 */
            app(ProductSkuService::class)->dealProductSpec($id, $item['product_list']);
            //处理相册
            $img_list = $item['img_list'];
            unset($item['img_list']);
            //处理图片
            app(ProductGalleryService::class)->updateProductGallery($id, $img_list);
            return $this->success('商品复制成功');
        } else {
            return $this->error('商品复制失败');
        }
    }


    /**
     * 获取分词
     * @return \think\Response
     */
    public function getParticiple(): \think\Response
    {
        $product_name = input('product_name');
        $keywords = htmlspecialchars(str_replace(' ', '', trim($product_name)));
        $keyword = app(ParticipleService::class)->cutForSearch($keywords);
        return $this->success([
            'keywords' => $keyword,
        ]);
    }

    /**
     * 运费模板列表
     * @return \think\Response
     */
    public function shippingTplList(): \think\Response
    {
        //运费模板
        $shipping_tpl_list = app(ShippingTplService::class)->getShippingTplList();
        return $this->success([
            'shipping_tpl_list' => $shipping_tpl_list,
        ]);
    }


    /**
     * 新增接口
     * @return \think\Response
     * @throws \exceptions\ApiException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function create(): \think\Response
    {
        $id = input('product_id/d', 0);
        $data = $this->request->only([
            'product_id' => $id,
            'product_name' => '',
            'product_desc_arr' => [],
            'product_sn' => '',
            'sort_order/d' => 50,
            'product_type/d' => 1,
            'check_status/d' => 1,
            'product_price/f' => 0.00,
            'is_promote/d' => 1,
            'promote_price/f' => 0.00,
            'promote_start_date' => Time::format(),
            'promote_end_date' => Time::format(strtotime('+1 month'), 'Y-m-d 23:59:59'),
            'product_tsn' => '',
            'virtual_sales/d' => 0,
            'limit_number/d' => 0,
            'category_id/d' => 0,
            'brand_id/d' => 0,
            'product_related' => [],
            'product_service_ids' => [],
            'market_price/f' => 0.00,
            'prepay_price/d' => 0,
            'keywords' => '',
            'product_brief' => '',
            'remark' => '',
            'product_stock/d' => 0,
            'product_weight/f' => 0.000,
            'is_best/d' => 0,
            'is_new/d' => 0,
            'is_hot/d' => 0,
            'product_status/d' => 1,
            'is_support_cod/d' => 0,
            'free_shipping/d' => 0,
            'product_video' => '',
            'integral/d' => 0,
            'rank_integral/d' => -1,
            'give_integral/d' => -1,
            'suppliers_id/d' => 0,
            'product_care' => '',
            'is_support_return/d' => 0,
            'shipping_tpl_id/d' => 0,
            'check_reason' => '',
            'product_article_list' => [],
            'img_list' => [],
            'shop_id' => request()->shopId,
            'shop_category_id' => 0
        ], 'post');
        validate(ProductValidate::class)->only(array_keys($data))->check($data);
        $result = $this->productService->updateProduct($id, $data, true);
        if ($result) {
            $id = $result;
            /* 处理属性 */
            app(ProductAttributesService::class)->dealProductAttr($id, input('attr_list/a', []));
            /* 处理规格 */
            app(ProductSkuService::class)->dealProductSpec($id, input('product_list/a', []));
            /* 处理会员价格 */
            app(ProductMemberPriceService::class)->dealMemberPrice($id, input('user_rank_list', []));
            //处理相册
            $img_list = $data['img_list'];
            unset($data['img_list']);
            //处理图片
            app(ProductGalleryService::class)->updateProductGallery($id, $img_list);
            return $this->success('商品管理添加成功');
        } else {
            return $this->error('商品管理更新失败');
        }
    }

    /**
     * 执行添加或更新操作
     *
     * @return \think\Response
     */
    public function update(): \think\Response
    {
        $id = input('product_id/d', 0);
        $data = $this->request->only([
            'product_id' => $id,
            'product_name' => '',
            'product_desc_arr' => [],
            'product_sn' => '',
            'sort_order/d' => 50,
            'product_type/d' => 1,
            'check_status/d' => 1,
            'product_price/f' => 0.00,
            'is_promote/d' => 1,
            'promote_price/f' => 0.00,
            'promote_start_date' => Time::format(),
            'promote_end_date' => Time::format(strtotime('+1 month'), 'Y-m-d 23:59:59'),
            'product_tsn' => '',
            'virtual_sales/d' => 0,
            'limit_number/d' => 0,
            'category_id/d' => 0,
            'brand_id/d' => 0,
            'product_related' => [],
            'product_service_ids' => [],
            'market_price/f' => 0.00,
            'prepay_price/d' => 0,
            'keywords' => '',
            'product_brief' => '',
            'remark' => '',
            'product_stock/d' => 0,
            'product_weight/f' => 0.000,
            'is_best/d' => 0,
            'is_new/d' => 0,
            'is_hot/d' => 0,
            'product_status/d' => 1,
            'is_support_cod/d' => 0,
            'free_shipping/d' => 0,
            'product_video' => '',
            'integral/d' => 0,
            'rank_integral/d' => -1,
            'give_integral/d' => -1,
            'suppliers_id/d' => 0,
            'product_care' => '',
            'is_support_return/d' => 0,
            'shipping_tpl_id/d' => 0,
            'check_reason' => '',
            'product_article_list' => [],
            'img_list' => [],
            'shop_category_id' => 0
        ], 'post');

        /* 处理属性 */
        if (input('attr_changed') == true) {
            app(ProductAttributesService::class)->dealProductAttr($id, input('attr_list/a', []));
        }
        /* 处理规格 */
        app(ProductSkuService::class)->dealProductSpec($id, input('product_list/a', []));
        /* 处理会员价格 */
        app(ProductMemberPriceService::class)->dealMemberPrice($id, input('user_rank_list', []));
        //处理相册
        $img_list = $data['img_list'];
        unset($data['img_list']);
        $result = $this->productService->updateProduct($id, $data, false);
        if ($result) {
            //处理图片
            app(ProductGalleryService::class)->updateProductGallery($id, $img_list);
            return $this->success('商品管理更新成功');
        } else {
            return $this->error('商品管理更新失败');
        }
    }

    /**
     * 更新单个字段
     *
     * @return \think\Response
     */
    public function updateField(): \think\Response
    {
        $id = input('id/d', 0);
        $field = input('field', '');

        if (!in_array($field, [
            'product_name',
            'product_sn',
            'product_stock',
            'is_show',
            'is_best',
            'is_new',
            'is_hot',
            'product_price',
            'sort_order',
            'product_status',
            'is_delete',
            'product_stock',
            'productSku'
        ])) {
            return $this->error('#field 错误');
        }
        $val = input('val');
        $data = [
            'product_id' => $id,
            $field => $val,
        ];
        if ($field == 'productSku') {
            foreach ($val as $k => $v) {
                $this->productService->updateSkuStock($v['sku_id'], $v['sku_stock']);
            }
        } else {
            $this->productService->updateProductField($id, $data);
        }

        return $this->success('更新成功');
    }

    /**
     * 回收站
     *
     * @return \think\Response
     */
    public function recycle(): \think\Response
    {
        $id = input('id/d', 0);
        $is_delete = input('is_delete/d', 0);
        $this->productService->recycleProduct($id);
        return $this->success('商品已移至回收站');
    }

    /**
     * 删除
     *
     * @return \think\Response
     */
    public function del(): \think\Response
    {
        $id = input('id/d', 0);
        $is_delete = input('is_delete/d', 0);
        $this->productService->deleteProduct($id);
        return $this->success('商品已删除');
    }

    /**
     * 批量操作
     *
     * @return \think\Response
     */
    public function batch(): \think\Response
    {
        if (empty(input('ids')) || !is_array(input('ids'))) {
            return $this->error('未选择项目');
        }

        if (input('type') == 'recycle') {
            foreach (input('ids') as $key => $id) {
                $id = intval($id);
                $this->productService->recycleProduct($id);
            }
            return $this->success('批量操作执行成功！');
        } elseif (input('type') == 'del') {
            foreach (input('ids') as $key => $id) {
                $id = intval($id);
                $this->productService->deleteProduct($id);
            }
            return $this->success('批量操作执行成功！');
        } else {
            return $this->error('#type 错误');
        }
    }
}
