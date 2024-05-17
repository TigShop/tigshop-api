<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 首页分类栏
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\decorate;

use app\model\product\Category;
use think\Model;

class MobileCatNav extends Model
{
    protected $pk = 'mobile_cat_nav_id';
    protected $table = 'mobile_cat_nav';
    protected $json = ["child_cat_ids", "brand_ids", "img_url"];
    protected $jsonAssoc = true;

    // 关联分类
    public function category()
    {
        return $this->hasOne(Category::class, 'category_id', 'category_id')->bind(["category_name"]);
    }

}
