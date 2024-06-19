<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 商品收藏
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\user;

use app\model\merchant\Shop;
use app\model\product\Product;
use app\model\product\ProductSku;
use think\Model;

class CollectShop extends Model
{
    protected $pk = 'collect_id';
    protected $table = 'collect_shop';
    protected $createTime = "add_time";
    protected $autoWriteTimestamp = true;

    // 关联商品
    public function shop()
    {
        return $this->hasOne(Shop::class, 'shop_id', 'shop_id');
    }

    // 关联用户
    public function user()
    {
        return $this->hasOne(User::class, 'user_id', 'user_id')->field(["user_id", "username", "rank_id", "discount"]);
    }

}
