<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 积分日志
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\user;

use app\model\user\UserPointsLog;
use app\service\core\BaseService;
use exceptions\ApiException;
use log\AdminLog;

/**
 * 积分日志服务类
 */
class UserPointsLogService extends BaseService
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
        $query = $this->filterQuery($filter)->with(["user"])->append(["change_type_name"]);
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
        $query = UserPointsLog::query();
        // 处理筛选条件
        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->where('change_desc', 'like', '%' . $filter['keyword'] . '%');
        }

        if (isset($filter['sort_field'], $filter['sort_order']) && !empty($filter['sort_field']) && !empty($filter['sort_order'])) {
            $query->order($filter['sort_field'], $filter['sort_order']);
        }

        if (isset($filter['user_id']) && !empty($filter["user_id"])) {
            $query->where('user_id', $filter["user_id"]);
        }
        return $query;
    }

    /**
     * 获取详情
     *
     * @param int $id
     * @return array
     * @throws ApiException
     */
    public function getDetail(int $id): array
    {
        $result = UserPointsLog::where('log_id', $id)->append(["change_type_name"])->find();

        if (!$result) {
            throw new ApiException('积分日志不存在');
        }

        return $result->toArray();
    }

    /**
     * 获取名称
     *
     * @param int $id
     * @return string|null
     */
    public function getName(int $id): ?string
    {
        return UserPointsLog::where('log_id', $id)->value('change_desc');
    }

    /**
     * 删除积分日志
     *
     * @param int $id
     * @return bool
     */
    public function deleteUserPointsLog(int $id): bool
    {
        if (!$id) {
            throw new ApiException('#id错误');
        }
        $get_name = $this->getName($id);
        $result = UserPointsLog::destroy($id);

        if ($result) {
            AdminLog::add('删除积分日志:' . $get_name);
        }

        return $result !== false;
    }
}
