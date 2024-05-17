<?php
//**---------------------------------------------------------------------+
//** 验证器文件 -- 友情链接
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\validate\setting;

use think\Validate;

class FriendLinksValidate extends Validate
{
    protected $rule = [
        'link_title' => 'require|max:100',
    ];

    protected $message = [
        'link_title.require' => '友情链接名称不能为空',
        'link_title.max' => '友情链接名称最多100个字符',
    ];

    protected $scene = [
        'create' => [
            'link_title',
        ],
        'update' => [
            'link_title',
        ],
    ];
}
