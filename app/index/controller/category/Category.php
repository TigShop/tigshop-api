<?php
//**---------------------------------------------------------------------+
//** 通用接口控制器文件 -- 商品分类
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\index\controller\category;

use app\index\IndexBaseController;
use app\service\api\admin\product\CategoryService;
use app\service\api\admin\product\ProductService;
use think\App;
use think\Response;

/**
 * 商品控制器
 */
class Category extends IndexBaseController
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
     * 商品
     *
     * @return Response
     */
    public function index(): Response
    {
        return $this->success([
        ]);
    }

    /**
     * 获取当前分类的父级分类
     * @return Response
     */
    public function parentTree(): Response
    {
        $id = input('id/d', 0);
        return $this->success([
            'category_tree' => app(CategoryService::class)->getParentCategoryTree($id),
        ]);
    }

    /**
     * 所有分类
     * @return Response
     */
    public function all(): Response
    {
        return $this->success([
            'category_tree' => app(CategoryService::class)->catList(),
        ]);
    }

    /**
     * 商品相关分类 -- 同级分类 - 同类其他品牌
     * @return Response
     * @throws \exceptions\ApiException
     */
    public function relateInfo(): Response
    {
        $filter = $this->request->only([
            "product_id/d" => 0,
            'size/d' => 10,
            'rank_num/d' => 5, // 排行榜显示数量
            'intro' => "hot",
        ], 'get');

        // 获取分类id
        $cate_id = app(ProductService::class)->getDetail($filter["product_id"])["category_id"];
        // 相关分类
        $relate_cate = app(CategoryService::class)->getRelatedCategory($cate_id, $filter);
        // 同类其他品牌
        $related_brand = app(CategoryService::class)->getOtherBrand($cate_id, $filter);
        // 同类排行榜
        $cate_rank = app(CategoryService::class)->getCategoryRank($cate_id, $filter);
        // 相关文章
        $article_list = app(CategoryService::class)->getArticleList($filter);
        // 看了还看
        $look_also = app(CategoryService::class)->getLookAlso($cate_id, $filter);

        return $this->success([
            'cate_info' => $relate_cate,
            'brand_info' => $related_brand,
            'cate_ranke' => $cate_rank,
            'article_list' => $article_list,
            'look_also' => $look_also,
            "filter" => $filter,
        ]);
    }

    /**
     * 相关分类
     * @return Response
     */
    public function getRelateCategory(): Response
    {
        $filter = $this->request->only([
            "product_id/d" => 0,
            'size/d' => 10,
            'rank_num/d' => 5, // 排行榜显示数量
            'intro' => "hot",
        ], 'get');
        // 获取分类id
        $cate_id = app(ProductService::class)->getDetail($filter["product_id"])["category_id"];
        // 相关分类
        $relate_cate = app(CategoryService::class)->getRelatedCategory($cate_id, $filter);
        return $this->success([
            'cate_info' => $relate_cate,
        ]);
    }

    /**
     * 相关品牌
     * @return Response
     */
    public function getRelateBrand(): Response
    {
        $filter = $this->request->only([
            "product_id/d" => 0,
            'size/d' => 10,
            'rank_num/d' => 5, // 排行榜显示数量
            'intro' => "hot",
        ], 'get');
        // 获取分类id
        $cate_id = app(ProductService::class)->getDetail($filter["product_id"])["category_id"];
        // 同类其他品牌
        $related_brand = app(CategoryService::class)->getOtherBrand($cate_id, $filter);
        return $this->success([
            'brand_info' => $related_brand,
        ]);
    }

    /**
     * 相关文章
     * @return Response
     */
    public function getRelateArticle(): Response
    {
        $filter = $this->request->only([
            "product_id/d" => 0,
            'size/d' => 10,
            'rank_num/d' => 5, // 排行榜显示数量
            'intro' => "hot",
        ], 'get');
        // 获取分类id
        $cate_id = app(ProductService::class)->getDetail($filter["product_id"])["category_id"];
        // 相关文章
        $article_list = app(CategoryService::class)->getArticleList($filter);
        return $this->success([
            'article_list' => $article_list,
        ]);
    }

    /**
     * 相关排行
     * @return Response
     */
    public function getRelateRank(): Response
    {
        $filter = $this->request->only([
            "product_id/d" => 0,
            'size/d' => 10,
            'rank_num/d' => 5, // 排行榜显示数量
            'intro' => "hot",
        ], 'get');
        // 获取分类id
        $cate_id = app(ProductService::class)->getDetail($filter["product_id"])["category_id"];
        // 同类排行榜
        $cate_rank = app(CategoryService::class)->getCategoryRank($cate_id, $filter);
        return $this->success([
            'cate_rank' => $cate_rank,
        ]);
    }

    /**
     * 相关看了还看
     * @return Response
     */
    public function getRelateLookAlso(): Response
    {
        $filter = $this->request->only([
            "product_id/d" => 0,
            'size/d' => 10,
            'rank_num/d' => 5, // 排行榜显示数量
            'intro' => "hot",
        ], 'get');
        // 获取分类id
        $cate_id = app(ProductService::class)->getDetail($filter["product_id"])["category_id"];
        // 看了还看
        $look_also = app(CategoryService::class)->getLookAlso($cate_id, $filter);
        return $this->success([
            'look_also' => $look_also,
        ]);
    }



    /**
     * 热门分类
     * @return Response
     */
    public function hot(): Response
    {
        $category = app(CategoryService::class)->getHotCategory();
        return $this->success([
            'category' => $category,
        ]);
    }
}
