<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 分类名称
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\content;

use app\model\content\ArticleCategory;
use app\service\core\BaseService;
use exceptions\ApiException;
use tig\CacheManager;

/**
 * 分类名称服务类
 */
class ArticleCategoryService extends BaseService
{
    protected ArticleCategory $articleCategoryModel;

    public function __construct(ArticleCategory $articleCategoryModel)
    {
        $this->articleCategoryModel = $articleCategoryModel;
    }

    /**
     * 获取筛选结果
     *
     * @param array $filter
     * @return array
     */
    public function getFilterResult(array $filter): array
    {
        $query = $this->filterQuery($filter);
        $result = $query->field('c.*, COUNT(s.article_category_id) AS has_children')
            ->leftJoin('article_category s', 'c.article_category_id = s.parent_id')
            ->group('c.article_category_id')->page($filter['page'], $filter['size'])->select();
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
        $query = $this->articleCategoryModel->query()->alias('c');
        // 处理筛选条件
        $query->where('c.parent_id', $filter['parent_id']);

        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->where('c.article_category_name', 'like', '%' . $filter['keyword'] . '%');
        }

        if (isset($filter['is_show']) && $filter['is_show'] > -1) {
            $query->where('c.is_show', $filter['is_show']);
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
     * @return ArticleCategory
     * @throws ApiException
     */
    public function getDetail(int $id): ArticleCategory
    {
        $result = $this->articleCategoryModel->where('article_category_id', $id)->find();

        if (!$result) {
            throw new ApiException('分类名称不存在');
        }

        return $result;
    }

    /**
     * 获取名称
     *
     * @param int $id
     * @return string|null
     */
    public function getName(int $id): ?string
    {
        return $this->articleCategoryModel->where('article_category_id', $id)->value('article_category_name');
    }

    /**
     * 验证权限
     * @param array $data
     * @param int $id
     * @return void
     * @throws ApiException
     */
    public function getCommunalData(array $data,int $id = 0):void
    {
        if (isset($data['parent_id'])) {
            /* 判断上级目录是否合法 */
            $children = $this->catAllChildIds($id); // 获得当前分类的所有下级分类
            unset($children[0]);
            if ($id) {
                if (in_array($data['parent_id'], $children)) {
                    /* 选定的父类是当前分类或当前分类的下级分类 */
                    throw new ApiException('所选择的上级分类不能是当前分类或者当前分类的下级分类');
                }
                if ($id == $data['parent_id']) {
                    /* 选定的父类是当前分类或当前分类的下级分类 */
                    throw new ApiException('所选择的上级分类不能是当前分类');
                }
            }
        }
    }

    /**
     * 添加文章分类
     * @param array $data
     * @return int
     * @throws ApiException
     */
    public function createArticleCat(array $data):int
    {
        $this->getCommunalData($data);
        $result = $this->articleCategoryModel->save($data);
        /* 清除文章分类缓存 */
        app(CacheManager::class)->clearCacheByTag('articleCate');
        return $this->articleCategoryModel->getKey();
    }

    /**
     * 执行分类名称更新
     *
     * @param int $id
     * @param array $data
     * @return bool
     * @throws ApiException
     */
    public function updateArticleCat(int $id, array $data):bool
    {
        $this->getCommunalData($data,$id);
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = $this->articleCategoryModel->where('article_category_id', $id)->save($data);
        /* 清除文章分类缓存 */
        app(CacheManager::class)->clearCacheByTag('articleCate');
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
    public function updateArticleCategoryField(int $id, array $data)
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = $this->articleCategoryModel::where('article_category_id', $id)->save($data);
        return $result !== false;
    }

    /**
     * 删除分类名称
     *
     * @param int $id
     * @return bool
     */
    public function deleteArticleCat(int $id): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = $this->articleCategoryModel->destroy($id);
        /* 清除文章分类缓存 */
        app(CacheManager::class)->clearCacheByTag('articleCate');
        return $result !== false;
    }

    /**
     * 获取所有分类列表 新方法
     * @param int $parent_id 获取该分类id下的所有分类（不含该分类）
     * @param bool $return_ids 是否返回分类id列表
     * @return array
     */
    public function catList(int $parent_id = 0): array
    {
        $data = cache('articleCateList');
        if ($data === null || $data === false) {
            $cat_list = $this->articleCategoryModel->alias('c')->field('c.article_category_id, c.article_category_name, c.parent_id,c.category_sn')
                ->order('c.parent_id, c.sort_order ASC, c.article_category_id ASC')->select();
            $cat_list = $cat_list ? $cat_list->toArray() : [];
            cache('articleCateList', $cat_list, 86400 * 100, 'articleCate');
        } else {
            $cat_list = $data;
        }
        $res = $this->xmsbGetDataTree($cat_list, $parent_id);
        return (array) $res;
    }

    /**
     * 获取指定分类id下的所有子分类id列表
     * @param int $parent_id 分类id
     * @return array
     */
    public function catAllChildIds(int $parent_id = 0): array
    {
        $cat_list = $this->catList($parent_id);
        $ids = [$parent_id];

        if (count($cat_list) !== count($cat_list, COUNT_RECURSIVE)) {
            $this->getChildrenIds($cat_list, $ids);
        }

        return $ids;
    }

    /**
     * 获取指定分类id下的所有子分类id列表
     * @param int $parent_id 分类id
     * @return array
     */
    public function getIssueParentId(): int
    {
        $category = $this->articleCategoryModel->Issue()->find();
        return $category ? $category['article_category_id'] : 0;
    }

    /**
     * 获取指定分类编号下的所有子分类id列表
     * @param int $category_sn 分类编号
     * @return array
     */
    public function getChildrenByCategorySn($category_sn): array
    {
        $category = $this->articleCategoryModel->where('category_sn', $category_sn)->find();
        if (empty($category)) {
            return [];
        }
        return $this->catList($category['article_category_id']);
    }

    /**
     * 获取帮助下的所有子分类id列表
     * @return array
     */
    public function getIssueChildIds(): array
    {
        $issueParentId = $this->getIssueParentId();
        if (!$issueParentId) {
            return [];
        }
        return $this->catAllChildIds($issueParentId);
    }

    public function getChildrenIds($category, &$ids)
    {
        if (!empty($category["children"])) {
            foreach ($category["children"] as $child) {
                $ids[] = $child['article_category_id'];
                $this->getChildrenIds($child, $ids);
            }
        }
    }

    /**
     * 无限级分类函数
     * @param array $arr 查询出的数据
     * @param int $first_parent 根节点主键值
     * @return array
     */
    public function xmsbGetDataTree(array $arr, int $first_parent = 0): array
    {
        $tree = ['article_category_id' => 0, 'parent_id' => 0];
        $tmpMap = [$first_parent => &$tree];
        foreach ($arr as $rk => $rv) {
            $tmpMap[$rv['article_category_id']] = $rv;
            $parentObj = &$tmpMap[$rv['parent_id']];
            if (!isset($parentObj['children'])) {
                $parentObj['children'] = [];
            }
            $parentObj['children'][] = &$tmpMap[$rv['article_category_id']];
        }
        return (array) $tree;
    }
}
