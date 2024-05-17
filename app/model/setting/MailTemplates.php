<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 邮件模板设置
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\setting;

use think\Model;

class MailTemplates extends Model
{
    protected $pk = 'template_id';
    protected $table = 'mail_templates';

    // 定义邮件模板名称
    const template_name = [
        "send_password" => "发送密码模板 [send_password]",
        "order_confirm" => "订单确认模板 [order_confirm]",
        "deliver_notice" => "发货通知模板 [deliver_notice]",
        "order_cancel" => "订单取消模板 [order_cancel]",
        "order_invalid" => "订单无效模板 [order_invalid]",
        "send_bonus" => "发送红包模板 [send_bonus]",
        "group_buy" => "团购商品模板 [group_buy]",
        "register_validate" => "邮件验证模板 [register_validate]",
        "virtual_card" => "虚拟卡片模板 [virtual_card]",
        "attention_list" => "关注管理 [attention_list]",
        "remind_of_new_order" => "新订单提醒模板 [remind_of_new_order]",
        "goods_booking" => "缺货回复模板 [goods_booking]",
        "user_message" => "留言回复模板 [user_message]",
        "recomment" => "用户评论回复模板 [recomment]",
        "order_pay_email" => "订单付款通知 [order_pay_email]",
    ];

    // 邮件模板名称
    public function getTemplateNameAttr($value, $data): string
    {
        return self::template_name[$data['template_code']] ?? "";
    }
}
