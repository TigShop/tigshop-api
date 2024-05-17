<?php
//**---------------------------------------------------------------------+
//** 验证器文件 -- 相册
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\validate\setting;

use think\Validate;

class GalleryValidate extends Validate
{
    protected $rule = [
        'gallery_name' => 'require|max:100',
    ];

    protected $message = [
        'gallery_name.require' => '相册名称不能为空',
        'gallery_name.max' => '相册名称最多100个字符',
    ];

    protected $scene = [
        'create' => [
            'gallery_name',
        ],
        'update' => [
            'gallery_name',
        ],
    ];
}
