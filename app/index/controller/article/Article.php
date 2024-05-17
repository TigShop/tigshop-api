<?php
//**---------------------------------------------------------------------+
//** 通用接口控制器文件 -- 文章
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\index\controller\article;

use app\index\IndexBaseController;
use app\service\content\ArticleService;
use app\service\product\ProductService;
use think\App;

/**
 * 文章控制器
 */
class Article extends IndexBaseController
{
    protected ArticleService $articleService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param ArticleService $articleService
     */
    public function __construct(App $app, ArticleService $articleService)
    {
        parent::__construct($app);
        $this->articleService = $articleService;
    }

    /**
     * 文章列表
     *
     * @return \think\Response
     */
    public function list(): \think\Response
    {
        $filter = $this->request->only([
            'article_ids/a' => [],
            'article_category_id' => 0,
            "category_sn" => "",
            "size/d" => 9,
            "page/d" => 1,
            'sort_field' => 'article_id',
            'sort_order' => 'desc',
        ], 'get');
        if ($filter['size'] > 50) {
            $filter['size'] = 50;
        }
        $filterResult = $this->articleService->getFilterList($filter);

        return $this->success([
            'filter_result' => $filterResult["list"],
            'filter' => $filter,
            'total' => $filterResult["count"],
        ]);
    }

    /**
     * 资讯类文章详情
     * @param int $id
     * @return \think\Response
     */
    public function newsInfo(): \think\Response
    {
        $id = input('id/d', 0);
        $item = $this->articleService->getNewsDetail($id);
        $next_prev = $this->articleService->getNextAndPrevDetail($id);
        if (!empty($item['product_ids'])) {
            $item['product_list'] = app(ProductService::class)->getFilterResult([
                'product_ids' => $item['product_ids'],
                'size' => 20,
                'sort_field_raw' => "field(product_id," . implode(',', $item['product_ids']) . ")",
            ]);
        }

        return $this->success([
            'item' => $item,
            'next' => $next_prev['next'],
            'prev' => $next_prev['prev'],
        ]);
    }

    /**
     * 帮助类文章详情
     * @param int $id
     * @return \think\Response
     */
    public function issueInfo(): \think\Response
    {
        $id = input('id/d', 0);
        $article_sn = input('article_sn', '');
        $item = $this->articleService->getIssueDetail($id, $article_sn);
        $next_prev = $this->articleService->getNextAndPrevDetail($item['article_id']);
        return $this->success([
            'item' => $item,
            'next' => $next_prev['next'],
            'prev' => $next_prev['prev'],
        ]);
    }

}
