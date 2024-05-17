<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 增票资质申请
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\finance;

use app\model\user\User;
use think\Model;

class UserInvoice extends Model
{
    protected $pk = 'invoice_id';
    protected $table = 'user_invoice';
    protected $createTime = 'add_time';
    protected $autoWriteTimestamp = true;

    // 关联用户
    public function user()
    {
        return $this->hasOne(User::class, 'user_id', 'user_id')->bind(['username']);
    }

    // 审核状态
    const STATUS_APPROVED = 1;
    const STATUS_AUDIT = 2;
    const STATUS_REJECTED = 3;
    const STATUS_NAME = [
        self::STATUS_APPROVED => '审核通过',
        self::STATUS_AUDIT => '待审核',
        self::STATUS_REJECTED => '审核未通过',
    ];

    // 抬头类型
    const TITLE_TYPE_PERSONAL = 1;
    const TITLE_TYPE_COMPANY = 2;

    const TITLE_TYPE_NAME = [
        self::TITLE_TYPE_PERSONAL => '个人',
        self::TITLE_TYPE_COMPANY => '企业',
    ];

    public function getTitleTypeNameAttr($value, $data)
    {
        return self::TITLE_TYPE_NAME[$data['title_type']] ?? '';
    }

    // 审核状态名称
    public function getStatusNameAttr($value, $data)
    {
        return self::STATUS_NAME[$data['status']] ?? '';
    }

    // 关键词检索 -- 会员名称 + 公司名称
    public function scopeKeyword($query, $value)
    {
        if (!empty($value)) {
            $query = $query->join('user', 'user_invoice.user_id = user.user_id')
                ->field('user_invoice.*')
                ->where(function ($query) use ($value) {
                    $query->where('user.username', 'like', "%$value%")
                        ->whereOr('user_invoice.company_name', 'like', "%$value%");
                });

        }
        return $query;
    }

}
