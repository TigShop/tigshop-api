<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- PC分类抽屉
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\decorate;

use think\Model;

class PcCatFloor extends Model
{
    protected $pk = 'cat_floor_id';
    protected $table = 'pc_cat_floor';
    protected $json = ["category_ids", "category_names", "brand_ids"];
    protected $jsonAssoc = true;
}
