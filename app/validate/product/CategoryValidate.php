<?php

namespace app\validate\product;

use think\Validate;

class CategoryValidate extends Validate
{
    protected $rule = [
        'category_name' => 'require|max:30',
    ];

    protected $message = [
        'category_name.require' => '分类名称不能为空',
        'category_name.max' => '分类名称最多30个字符',
    ];

    protected $scene = [
        'create' => [
            'category_name',
        ],
        'update' => [
            'category_name',
        ],
    ];
}
