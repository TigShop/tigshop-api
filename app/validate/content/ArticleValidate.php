<?php
//**---------------------------------------------------------------------+
//** 验证器文件 -- 文章标题
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\validate\content;

use think\Validate;

class ArticleValidate extends Validate
{
    protected $rule = [
        'article_title' => 'require|max:100',
    ];

    protected $message = [
        'article_title.require' => '文章标题名称不能为空',
        'article_title.max' => '文章标题名称最多100个字符',
    ];

    protected $scene = [
        'create' => [
            'article_title',
        ],
        'update' => [
            'article_title',
        ],
    ];
}
