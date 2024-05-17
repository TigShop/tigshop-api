<?php
//**---------------------------------------------------------------------+
//** 验证器文件 -- 邮件模板设置
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\validate\setting;

use think\Validate;

class MailTemplatesValidate extends Validate
{
    protected $rule = [
        'template_subject' => 'require|max:100',
        'template_content' => 'require',
    ];

    protected $message = [
        'template_subject.require' => '邮件的主题不能为空',
        'template_subject.max' => '邮件的主题最多100个字符',
        'template_content.require' => '邮件的内容不能为空',
    ];

    protected $scene = [
        'create' => [
            'template_subject',
            'template_content'
        ],
        'update' => [
            'template_subject',
            'template_content'
        ],
    ];
}
