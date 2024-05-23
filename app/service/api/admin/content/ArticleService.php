<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 文章标题
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\content;

use app\model\content\Article;
use app\model\content\ArticleCategory;
use app\model\product\ProductArticle;
use app\service\api\admin\BaseService;
use exceptions\ApiException;

/**
 * 文章标题服务类
 */
class ArticleService extends BaseService
{
    protected Article $articleModel;

    public function __construct(Article $articleModel)
    {
        $this->articleModel = $articleModel;
    }

    /**
     * 获取筛选结果
     *
     * @param array $filter
     * @return array
     */
    public function getFilterResult(array $filter): array
    {
        $query = $this->filterQuery($filter)->with(['category_name']);
        $result = $query->page($filter['page'],
            $filter['size'])->append(['article_type_text'])->hidden(['content'])->select();
        return $result->toArray();
    }

    /**
     * 获取筛选结果数量
     *
     * @param array $filter
     * @return int
     */
    public function getFilterCount(array $filter): int
    {
        $query = $this->filterQuery($filter);
        $count = $query->count();
        return $count;
    }

    /**
     * 筛选查询
     *
     * @param array $filter
     * @return object
     */
    protected function filterQuery(array $filter): object
    {
        $query = $this->articleModel->query();
        // 处理筛选条件

        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->where('article_title', 'like', '%' . $filter['keyword'] . '%');
        }

        if (isset($filter['is_show']) && $filter['is_show'] > -1) {
            $query->where('is_show', $filter['is_show']);
        }

        if (isset($filter['is_hot']) && $filter['is_hot'] > -1) {
            $query->where('is_hot', $filter['is_hot']);
        }

        // 文章分类检索
        if (isset($filter['article_category_id']) && !empty($filter['article_category_id'])) {
            $query->whereIn('article_category_id', app(ArticleCategoryService::class)->catAllChildIds(end($filter['article_category_id'])));
        }

        // 分类编码
        if (isset($filter["category_sn"]) && !empty($filter["category_sn"])) {
            $cate_id = ArticleCategory::where('category_sn', $filter['category_sn'])->value('article_category_id');
            $query->whereIn('article_category_id', app(ArticleCategoryService::class)->catAllChildIds($cate_id));
        }

        if (isset($filter['article_ids']) && !empty($filter["article_ids"])) {
            $query->whereIn('article_id', $filter['article_ids']);
        }

        if (isset($filter['sort_field'], $filter['sort_order']) && !empty($filter['sort_field']) && !empty($filter['sort_order'])) {
            $query->order($filter['sort_field'], $filter['sort_order']);
        }
        return $query;
    }

    /**
     * 获取详情
     *
     * @param int $id
     * @return Article
     * @throws ApiException
     */
    public function getDetail(int $id): Article
    {
        $result = $this->articleModel->with(["product_article"])->where('article_id', $id)->append(['article_type_text'])->find();

        if (!$result) {
            throw new ApiException('文章标题不存在');
        }

        return $result;
    }

    /**
     * 获取资讯类详情
     *
     * @param int $id
     * @return array
     * @throws ApiException
     */
    public function getNewsDetail(int $id): array
    {
        $result = $this->articleModel->where('article_id', $id)->append(['article_type_text'])->find();
        if (!$result) {
            throw new ApiException(/** LANG */'文章不存在');
        }
        $issueChildIds = app(ArticleCategoryService::class)->getIssueChildIds();

        if ($issueChildIds && in_array($result['article_category_id'], $issueChildIds)) {
            throw new ApiException(/** LANG */'文章不存在');
        }
        $product = ProductArticle::where('article_id', $result['article_id'])->column('goods_id');
        $result->product_ids = $product;
        $this->articleModel->where('article_id', $id)->inc('click_count')->update();
        return $result->toArray();
    }

    /**
     * 获取文章上下下一篇详情
     *
     * @param int $id
     * @return array
     * @throws ApiException
     */
    public function getNextAndPrevDetail(int $id): array
    {
        $result = $this->articleModel->where('article_id', $id)->find();
        $next = $this->articleModel->where('article_category_id', $result['article_category_id'])->where('article_id', '>', $id)->where('is_show', 1)->limit(1)->order('article_id', 'asc')->field(['article_id', 'article_title'])->find();
        $prev = $this->articleModel->where('article_category_id', $result['article_category_id'])->where('article_id', '<', $id)->where('is_show', 1)->limit(1)->order('article_id', 'desc')->field(['article_id', 'article_title'])->find();
        return ['prev' => $prev, 'next' => $next];
    }

    /**
     * 获取帮助类详情
     *
     * @param int $id
     * @return array
     * @throws ApiException
     */
    public function getIssueDetail(int $id, string $article_sn = ''): array
    {
        if (!empty($article_sn)) {
            $result = $this->articleModel->where('article_sn', $article_sn)->append(['article_type_text'])->find();
        } else {
            $result = $this->articleModel->where('article_id', $id)->append(['article_type_text'])->find();
        }


        if (!$result) {
            throw new ApiException(/** LANG */'文章不存在');
        }
        $issueChildIds = app(ArticleCategoryService::class)->getIssueChildIds();

        if (!$issueChildIds || !in_array($result['article_category_id'], $issueChildIds)) {
            throw new ApiException(/** LANG */'文章不存在');
        }

        return $result->toArray();
    }

    /**
     * 获取名称
     *
     * @param int $id
     * @return string|null
     */
    public function getName(int $id): ?string
    {
        return $this->articleModel->where('article_id', $id)->value('article_title');
    }

    /**
     * 添加文章
     * @param array $data
     * @return int
     * @throws \Exception
     */
    public function createArticle(array $data):int
    {
        $result = $this->articleModel->save($data);
        $lastId = $this->articleModel->getKey();
        // 关联商品
        if (!empty($data['product_ids'])) {
            foreach ($data["product_ids"] as $k => $v) {
                $product_article_list[$k] = [
                    "goods_id" => $v,
                    "article_id" => $this->articleModel->article_id,
                ];
            }
            (new ProductArticle)->saveAll($product_article_list);
        }
        return $lastId;
    }

    /**
     * 执行文章更新
     * @param int $id
     * @param array $data
     * @return bool
     * @throws ApiException
     */
    public function updateArticle(int $id, array $data):bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }

        $product_ids = $data['product_ids'];
        unset($data['product_ids']);

        $result = $this->articleModel->where('article_id', $id)->save($data);

        // 关联商品
        if (!empty($product_ids)) {
            ProductArticle::where('article_id', $id)->delete();
            foreach ($product_ids as $k => $v) {
                $product_article_list[$k] = [
                    "goods_id" => $v,
                    "article_id" => $id,
                ];
            }
            (new ProductArticle)->saveAll($product_article_list);
        }
        return $result !== false;
    }

    /**
     * 更新单个字段
     *
     * @param int $id
     * @param array $data
     * @return int|bool
     * @throws ApiException
     */
    public function updateArticleField(int $id, array $data)
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = $this->articleModel::where('article_id', $id)->save($data);
        return $result !== false;
    }

    /**
     * 删除文章标题
     *
     * @param int $id
     * @return bool
     */
    public function deleteArticle(int $id): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = $this->articleModel->destroy($id);
        return $result !== false;
    }


    /**
     * 批量操作
     * @param int $id
     * @param string $type
     * @param array $target_cat
     * @return bool
     * @throws ApiException
     */
    public function batchOperation(int $id,string $type,array $target_cat = []): bool
    {
        if(empty($type)){
            throw new ApiException(/** LANG */'#type 错误');
        }
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $article = Article::find($id);

        switch ($type){
            case "del":
                // 删除
                $result = $article->destroy($id);
                break;
            case "show":
                // 显示
                $result = $article->save(['is_show' => 1]);
                break;
            case "hide":
                // 隐藏
                $result = $article->save(['is_show' => 0]);
                break;
            case "move_cat":
                // 转移分类
                $result = $article->save(['article_category_id' => end($target_cat)]);
                break;
        }
        return $result !== false;
    }


    /**
     * 获取文章列表
     * @param array $filter
     * @return array
     */
    public function getFilterList(array $filter): array
    {
        $query = $this->filterQuery($filter);
        $query = $query->where('is_show', 1);
        $count = $query->count();
        $list = $query->hidden(['content'])
            ->page($filter["page"] ?? 1, $filter['size'] ?? 10)->select()->toArray();

        return [
            'list' => $list,
            'count' => $count,
        ];
    }
}
