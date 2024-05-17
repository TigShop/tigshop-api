<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 分类名称
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\content;

use think\Model;

class ArticleCategory extends Model
{
    protected $pk = 'article_category_id';
    protected $table = 'article_category';

    const CATEGORY_SN_ISSUE = 'bzzx'; //帮助中心文章分类
    // 查询帮助的分类
    public function scopeIssue($query)
    {
        $query->where('category_sn', self::CATEGORY_SN_ISSUE);
    }
}
