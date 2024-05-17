<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 示例模板
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\example;

use think\Model;

class Example extends Model
{
    protected $pk = 'example_id';
    protected $table = 'example';

    /**
     * 该数据能不能删除
     * @param $userId
     * @return bool
     */
    public function canDelete($userId): bool
    {
        if ($this->user_id != $userId) {
            return false;
        }
        if ($this->status == 1) {
            return true;
        }
        return false;
    }

    /**
     * 该数据能不能编辑
     * @param $userId
     * @return bool
     */
    public function canEdit($userId): bool
    {
        if ($this->user_id != $userId) {
            return false;
        }
        if ($this->status == 1) {
            return true;
        }
        return false;
    }
}
