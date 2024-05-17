<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 发票申请
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\finance;

use app\model\order\Order;
use app\model\user\User;
use think\Model;

class OrderInvoice extends Model
{
    protected $pk = 'id';
    protected $table = 'order_invoice';

    protected $createTime = "add_time";
    protected $autoWriteTimestamp = true;

    // 关联用户
    public function user()
    {
        return $this->hasOne(User::class, 'user_id', 'user_id')->bind(['username']);
    }

    // 关联订单
    public function orderInfo()
    {
        return $this->hasOne(Order::class, 'order_id', 'order_id')->bind(["order_sn", "total_amount"]);
    }

    // 关联增票资质
    public function userInvoice()
    {
        return $this->hasOne(UserInvoice::class, 'user_id', 'user_id')->append(["status_name"]);
    }

    // 发票类型
    const INVOICE_TYPE_ORDINARY = 1;
    const INVOICE_TYPE_SPECIAL = 2;
    const INVOICE_TYPE_NAME = [
        self::INVOICE_TYPE_ORDINARY => '普通发票',
        self::INVOICE_TYPE_SPECIAL => '增值税专用发票',
    ];

    //发票申请状态
    const STATUS_WAIT = 0;
    const STATUS_PASS = 1;
    const STATUS_REFUSE = 2;
    const STATUS_NAME = [
        self::STATUS_WAIT => '待处理',
        self::STATUS_PASS => '已开',
        self::STATUS_REFUSE => '失败/作废',
    ];

    // 发票抬头
    const TITLE_TYPE_PERSONAL = 1;
    const TITLE_TYPE_COMPANY = 2;
    const TITLE_TYPE_NAME = [
        self::TITLE_TYPE_PERSONAL => '个人',
        self::TITLE_TYPE_COMPANY => '公司',
    ];
    public function getTitleTypeNameAttr($value, $data): string
    {
        return self::TITLE_TYPE_NAME[$data['title_type']] ?? '';
    }

    public function getInvoiceTypeNameAttr($value, $data): string
    {
        return self::INVOICE_TYPE_NAME[$data['invoice_type']] ?? '';
    }

    public function getStatusNameAttr($value, $data): string
    {
        return self::STATUS_NAME[$data['status']] ?? '';
    }

    // 关键词检索 -- 会员名称 + 公司名称 + 订单编号
    public function scopeKeyword($query, $value)
    {
        if (!empty($value)) {
            return $query->where(function ($query) use ($value) {
                $query->where('user.username', 'like', '%' . $value . '%')
                    ->whereOr('order.order_sn', 'like', '%' . $value . '%')
                    ->whereOr('order_invoice.company_name', 'like', '%' . $value . '%');
            });
        }
        return $query;
    }

}
