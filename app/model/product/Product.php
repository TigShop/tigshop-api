<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 商品管理
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\product;

use app\model\promotion\SeckillItem;
use app\service\api\admin\product\CategoryService;
use think\Model;
use utils\Time;

class Product extends Model
{
    protected $pk = 'product_id';
    protected $table = 'product';
    protected $json = ['product_related', 'product_service_ids'];
    // 设置JSON数据返回数组
    protected $jsonAssoc = true;

    // 关联品牌
    public function brand()
    {
        return $this->hasOne(Brand::class, 'brand_id', 'brand_id')->bind(["brand_name", "brand_logo", "first_word", "is_show"]);
    }

    // 关联商品分类
    public function category()
    {
        return $this->hasOne(Category::class, 'category_id', 'category_id')->bind(["category_name", "parent_id", "is_show"]);
    }
    public function seckillMinPrice()
    {
        // 获取当前时间
        $now = Time::now();
        // 使用hasOne关联定义
        return $this->hasOne(SeckillItem::class, 'product_id', 'product_id')->bind(['seckill_price'])->order('seckill_price', 'asc');
    }

    // 关联商品属性规格
    public function productSku()
    {
        return $this->hasMany(ProductSku::class, 'product_id', 'product_id');
    }

    // 审核状态
    const CHECK_STATUS_PENDING = 0; // 待审核
    const CHECK_STATUS_APPROVED = 1; // 审核通过
    const CHECK_STATUS_REJECTED = 2; // 审核未通过
    protected const CHECK_STATUS_MAP = [
        self::CHECK_STATUS_PENDING => '待审核',
        self::CHECK_STATUS_APPROVED => '审核通过',
        self::CHECK_STATUS_REJECTED => '审核未通过',
    ];
    // 推荐类型
    public function scopeIntroType($query, $value)
    {
        if (in_array($value, ['new', 'hot', 'best'])) {
            $value = 'is_' . $value;
        }
        if (!in_array($value, ['is_new', 'is_hot', 'is_best'])) {
            return $query;
        }
        return $query->where($value, 1);
    }

    // 获取分类名称
    public function getCategoryTreeNameAttr($value, $data)
    {
        $category_name = app(CategoryService::class)->getParentCategory($data['category_id'])["category_name"];
        return implode('|', $category_name);

    }

    // 查询店铺平台订单
    public function scopeStorePlatform($query)
    {
        if (request()->shopId > 0) {
            return $query->where('shop_id', request()->shopId);
        } else {
            return $query;
        }
    }

    // 促销时间格式转换
    public function getPromoteStartDateAttr($value)
    {
        return Time::format($value);
    }

    public function getPromoteEndDateAttr($value)
    {
        return Time::format($value);
    }

}
