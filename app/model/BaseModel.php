<?php

namespace app\model;

use think\Model;

class BaseModel extends Model
{
    /**
     * 获取全部数据
     * @param array $where
     * @param string $field
     * @return mixed
     */
    public function getAll($where = [], $field = '*')
    {
        return $this->field($field)->where($where)->select()->toArray();
    }

    /**
     * 获取单条数据--查询数据不存在的时候返回空数组
     * @param array $where
     * @param string $field
     * @return mixed
     */
    public function getRow($where = [], $field = '*')
    {
        return $this->field($field)->where($where)->findOrEmpty();
    }

    /**
     * 获取指定字段值
     * @param array $where
     * @param string $field
     * @return mixed
     */
    public function getOne($where = ['user_id' => 1], $field = '*')
    {
        return $this->where($where)->value($field);
    }

    /**
     * 获取指定字段返回值
     * @param array $where
     * @param string $field
     * @return array
     */
    public function getCol($where = [], $field = '*')
    {
        return $this->where($where)->column($field);
    }
}