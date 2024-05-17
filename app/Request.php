<?php

namespace app;

/**
 * 重写Request一些方法
 */
class Request extends \think\Request

{
    /**
     * 获取指定的参数
     * @access public
     * @param array $name 变量名
     * @param mixed $data 数据或者变量类型
     * @param string|array|null $filter 过滤方法
     * @return array
     */
    public function only(array $name, $data = 'param', string|array|null $filter = '', bool $hasDefault = true): array
    {
        $item = [];
        foreach ($name as $key => $val) {
            if (!$hasDefault) {
                $key = $val;
                $val = null;
            }
            $_key = explode('/', $key);
            $item[$_key[0]] = $this->$data($key, $val ?? null, $filter);
            if (is_string($item[$_key[0]]) && $hasDefault) {
                // 值为string时进行统一trim处理
                if ($item[$_key[0]] === '' && !empty($val)) {
                    // 当字符型请求为空且设置了默认值时，采用默认值（由isset判断改为!empty判断）
                    $item[$_key[0]] = $val;
                }
                $item[$_key[0]] = trim($item[$_key[0]]);
            }
        }
        return $item;
    }
}
