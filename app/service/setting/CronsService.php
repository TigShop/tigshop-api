<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 计划任务
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\setting;

use app\common\exceptions\ApiException;
use app\common\log\AdminLog;
use app\model\setting\Crons;
use app\service\BaseService;

/**
 * 计划任务服务类
 */
class CronsService extends BaseService
{
    public function __construct()
    {
    }

    /**
     * 获取筛选结果
     *
     * @param array $filter
     * @return array
     */
    public function getFilterResult(array $filter): array
    {
        $query = $this->filterQuery($filter)->append(["cron_type_name"]);
        $result = $query->page($filter['page'], $filter['size'])->select();
        return $result->toArray();
    }

    /**
     * 获取筛选结果数量
     *
     * @param array $filter
     * @return int
     */
    public function getFilterCount(array $filter): int
    {
        $query = $this->filterQuery($filter);
        $count = $query->count();
        return $count;
    }

    /**
     * 筛选查询
     *
     * @param array $filter
     * @return object
     */
    public function filterQuery(array $filter): object
    {
        $query = Crons::query();
        // 处理筛选条件

        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->where('cron_name', 'like', '%' . $filter['keyword'] . '%');
        }

        if (isset($filter['sort_field'], $filter['sort_order']) && !empty($filter['sort_field']) && !empty($filter['sort_order'])) {
            $query->order($filter['sort_field'], $filter['sort_order']);
        }
        return $query;
    }

    /**
     * 获取详情
     *
     * @param int $id
     * @return Crons
     * @throws ApiException
     */
    public function getDetail(int $id): Crons
    {
        $result = Crons::where('cron_id', $id)->append(["cron_type_name"])->find();

        if (!$result) {
            throw new ApiException(/** LANG */'计划任务不存在');
        }

        return $result;
    }

    /**
     * 获取名称
     *
     * @param int $id
     * @return string|null
     */
    public function getName(int $id): ?string
    {
        return Crons::where('cron_id', $id)->value('cron_name');
    }

    /**
     * 创建计划任务
     * @param array $data
     * @return int
     */
    public function createCrons(array $data): int
    {
        $result = Crons::create($data);
        AdminLog::add('新增计划任务:' . $data['cron_name']);
        return $result->getKey();
    }


    /**
     * 执行计划任务更新
     * @param int $id
     * @param array $data
     * @return bool
     * @throws ApiException
     */
    public function updateCrons(int $id, array $data): bool
    {
        if (!$id) {
            throw new ApiException('#id错误');
        }
        $result = Crons::where('cron_id', $id)->save($data);
        AdminLog::add('更新计划任务:' . $this->getName($id));
        return $result !== false;
    }

    /**
     * 更新单个字段
     *
     * @param int $id
     * @param array $data
     * @return int|bool
     * @throws ApiException
     */
    public function updateCronsField(int $id, array $data)
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = Crons::where('cron_id', $id)->save($data);
        AdminLog::add('更新计划任务:' . $this->getName($id));
        return $result !== false;
    }

    /**
     * 删除计划任务
     *
     * @param int $id
     * @return bool
     */
    public function deleteCrons(int $id): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $get_name = $this->getName($id);
        $result = Crons::destroy($id);

        if ($result) {
            AdminLog::add('删除计划任务:' . $get_name);
        }

        return $result !== false;
    }
}
