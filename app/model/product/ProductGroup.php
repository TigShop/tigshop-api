<?php
//**---------------------------------------------------------------------+
//**   品牌模型
//**---------------------------------------------------------------------+
//**   版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//**   作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//**   提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\product;

use think\Model;

class ProductGroup extends Model
{
    protected $pk = 'product_group_id';
    protected $table = 'product_group';
    protected $autoWriteTimestamp = 'int';
    protected $createTime = 'add_time';
    protected $json = ['product_ids'];
    protected $jsonAssoc = true;
}
