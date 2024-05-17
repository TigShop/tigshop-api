<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 文章标题
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\content;

use app\common\utils\Time;
use app\model\product\ProductArticle;
use think\Model;

class Article extends Model
{
    protected $pk = 'article_id';
    protected $table = 'article';

    protected $createTime = 'add_time';
    protected $autoWriteTimestamp = 'int';

    // 关联商品文章
    public function productArticle()
    {
        return $this->hasMany(ProductArticle::class, 'article_id', 'article_id');
    }

    const COMMON = 1;
    const HELP = 2;
    // 文章类型映射
    protected const ARTICLE_TYPE_MAP = [
        self::COMMON => '普通文章',
        self::HELP => '帮助文章',
    ];

    public function categoryName()
    {
        return $this->hasOne(ArticleCategory::class, "article_category_id", "article_category_id")->bind(['article_category_name']);
    }

    // 字段处理
    public function getAddTimeAttr($value): string
    {
        return Time::format($value);
    }

    public function getArticleTypeTextAttr($value, $data): string
    {
        return self::ARTICLE_TYPE_MAP[$data['article_type']] ?? '';
    }
}
