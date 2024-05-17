<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 地区管理
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\setting;

use think\Model;

class Region extends Model
{
    protected $pk = 'region_id';
    protected $table = 'region';

    // 使用hasChildren方法来判断Region是否有子区域
    public function hasChildren()
    {
        // 假设 'parent_id' 是关联Region模型的键
        return $this->children()->count() > 0;
    }
    // 定义子地区的关联关系
    public function children()
    {
        return $this->hasMany(Region::class, 'parent_id', 'region_id');
    }
    public function getLeafAttr($value, $data)
    {
        // withCount会预载入children_count属性，我们可以用它来设置leaf属性
        // 如果children_count大于0，则不是叶节点，否则是叶节点
        return !isset($data['children_count']) || $data['children_count'] == 0;
    }
    // 在输出数组或JSON时隐藏children_count字段
    protected $hidden = ['children_count'];

}
