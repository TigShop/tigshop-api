<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 文章
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\admin\controller\content;

use app\admin\AdminBaseController;
use app\service\api\admin\content\ArticleService;
use app\validate\content\ArticleValidate;
use exceptions\ApiException;
use think\App;
use think\exception\ValidateException;
use think\facade\Db;
use think\Response;

/**
 * 文章标题控制器
 */
class Article extends AdminBaseController
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
        $this->checkAuthor('articleManage'); //权限检查
    }

    /**
     * 列表页面
     *
     * @return Response
     */
    public function list(): Response
    {
        $filter = $this->request->only([
            'keyword' => '',
            'is_show' => -1,
            'is_hot' => -1,
            'article_category_id' => [],
            'article_ids' => [],
            'page/d' => 1,
            'size/d' => 15,
            'sort_field' => 'article_id',
            'sort_order' => 'desc',
        ], 'get');

        $filterResult = $this->articleService->getFilterResult($filter);
        $total = $this->articleService->getFilterCount($filter);

        return $this->success([
            'filter_result' => $filterResult,
            'filter' => $filter,
            'total' => $total,
        ]);
    }

    /**
     * 详情
     * @return Response
     */
    public function detail(): Response
    {
        $id = input('id/d', 0);
        $item = $this->articleService->getDetail($id)->toArray();
        $item["product_ids"] = array_column($item['product_article'], 'goods_id');
        unset($item["product_article"]);
        return $this->success([
            'item' => $item,
        ]);
    }

    /**
     * 获取请求数据
     * @return array
     */
    public function requestData(): array
    {
        $data = $this->request->only([
            'article_title' => '',
            'article_category_id' => 0,
            'article_sn' => '',
            'article_thumb' => '',
            'article_tag' => '',
            'article_type' => 0,
            'content' => '',
            'description' => '',
            'keywords' => '',
            'is_show' => 0,
            'is_hot' => 0,
            'is_top' => 0,
            'click_count' => 0,
            'product_ids' => [],
            'link' => '',
        ], 'post');

        return $data;
    }

    /**
     * 添加
     * @return Response
     */
    public function create(): Response
    {
        $data = $this->requestData();
        try {
            validate(ArticleValidate::class)
                ->scene('create')
                ->check($data);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        $result = $this->articleService->createArticle($data);
        if ($result) {
            return $this->success(/** LANG */'文章标题添加成功');
        } else {
            return $this->error(/** LANG */'文章标题更新失败');
        }
    }

    /**
     * 执行更新操作
     * @return Response
     */
    public function update(): Response
    {
        $id = input('id/d', 0);
        $data = $this->requestData();
        $data["article_id"] = $id;
        try {
            validate(ArticleValidate::class)
                ->scene('update')
                ->check($data);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        $result = $this->articleService->updateArticle($id, $data);
        if ($result) {
            return $this->success(/** LANG */'文章标题更新成功');
        } else {
            return $this->error(/** LANG */'文章标题更新失败');
        }
    }

    /**
     * 更新单个字段
     *
     * @return Response
     */
    public function updateField(): Response
    {
        $id = input('id/d', 0);
        $field = input('field', '');

        if (!in_array($field, ['article_title', 'article_sn', 'sort_order', 'is_hot', 'is_show'])) {
            return $this->error(/** LANG */'#field 错误');
        }

        $data = [
            'article_id' => $id,
            $field => input('val'),
        ];

        $this->articleService->updateArticleField($id, $data);

        return $this->success(/** LANG */'更新成功');
    }

    /**
     * 删除
     *
     * @return Response
     */
    public function del(): Response
    {
        $id = input('id/d', 0);
        $this->articleService->deleteArticle($id);
        return $this->success(/** LANG */'指定项目已删除');
    }

    /**
     * 批量操作
     *
     * @return Response
     */
    public function batch(): Response
    {
        if (empty(input('ids')) || !is_array(input('ids'))) {
            return $this->error(/** LANG */'未选择项目');
        }

        // 转移的分类
        $target_cat = input('target_cat/a', []);

        if (in_array(input('type'),['del','show',"hide","move_cat"])) {
            try {
                //批量操作一定要事务
                Db::startTrans();
                foreach (input('ids') as $id) {
                    $id = intval($id);
                    $this->articleService->batchOperation($id, input('type'),$target_cat);
                }
                Db::commit();
            } catch (\Exception $exception) {
                Db::rollback();
                throw new ApiException($exception->getMessage());
            }

            return $this->success(/** LANG */'批量操作执行成功！');
        } else {
            return $this->error(/** LANG */'#type 错误');
        }
    }
}
