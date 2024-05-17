<?php
//**---------------------------------------------------------------------+
//** 验证器文件 -- 分类名称
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\validate\content;

use think\facade\Db;
use think\Validate;

class ArticleCategoryValidate extends Validate
{
    protected $rule = [
        'article_category_name' => 'require|max:100|checkUnique',
        'category_sn' => 'checkUnique',
    ];

    protected $message = [
        'article_category_name.require' => '分类名称名称不能为空',
        'article_category_name.max' => '分类名称名称最多100个字符',
        'category_sn.checkUnique' => '该分类编码已存在',
        'article_category_name.checkUnique' => '该分类名称已存在',
    ];

    protected $scene = [
        'create' => [
            'article_category_name',
            'category_sn'
        ],
        'update' => [
            'article_category_name',
            'category_sn',
        ],
    ];

    // 验证唯一
    public function checkUnique($value, $rule, $data = [], $field = '')
    {
        $id = isset($data['article_category_id']) ? $data['article_category_id'] : 0;
        $query = Db::name('article_category')->where($field, $value)->where('article_category_id', '<>', $id);
        return $query->count() === 0;
    }
}
