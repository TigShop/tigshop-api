<?php
//**---------------------------------------------------------------------+
//** 通用接口控制器文件 -- 文章分类
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\index\controller\article;

use app\index\IndexBaseController;
use app\service\api\admin\content\ArticleCategoryService;
use app\service\api\admin\content\ArticleService;
use think\App;

/**
 * 文章分类控制器
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
     * 文章分类
     * @param $parent_sn
     * @return \think\Response
     */
    public function list(): \think\Response
    {
        $category_sn = input('category_sn', '');
        $list = app(ArticleCategoryService::class)->getChildrenByCategorySn($category_sn);
        return $this->success([
            'filter_result' => $list,
        ]);
    }

    /**
     * 首页帮助分类与文章
     * @return \think\Response
     */
    public function indexBzzxList(): \think\Response
    {
        $category_size = input('category_size', 5);
        $article_size = input('article_size', 4);
        $list = app(ArticleCategoryService::class)->getChildrenByCategorySn('bzzx');
        $top5 = array_slice($list['children'], 0, $category_size);
        foreach ($top5 as $k => &$v) {
            $v['articles'] = app(ArticleService::class)->getFilterResult([
                'size' => $article_size,
                'is_show' => 1,
                'page' => 1,
                'article_category_id' => [$v['article_category_id']],
            ]);
        }
        return $this->success([
            'filter_result' => $top5,
        ]);
    }

}
