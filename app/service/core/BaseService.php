<?php

namespace app\service\core;

use think\Model;
use think\model\Collection;

abstract class BaseService
{

    protected Model $model;

    /**
     * 获取筛选结果数量
     *
     * @param array $filter
     * @return int
     */
    public function getFilterCount(array $filter): int
    {
        $query = $this->filterQuery($filter);
        return $query->count();
    }

    /**
     * 筛选查询
     *
     * @param array $filter
     * @return object
     */
    protected function filterQuery(array $filter): object
    {
        return $this->model;
    }

    /**
     * 获取筛选结果
     *
     * @param array $filter
     * @return array
     */
    public function getFilterList(array $filter, array $with = [], array $append = []): Collection
    {
        $query = $this->filterQuery($filter);
        if ($with) {
            $query = $query->with($with);
        }
        if ($append) {
            $query = $query->append($append);
        }
        return $query->page($filter['page'], $filter['size'])->select();
    }

}
