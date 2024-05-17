<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 会员等级
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\user;

use app\common\exceptions\ApiException;
use app\common\log\AdminLog;
use app\model\user\UserRank;
use app\service\BaseService;
use app\validate\user\UserRankValidate;

/**
 * 会员等级服务类
 */
class UserRankService extends BaseService
{
    protected UserRank $userRankModel;
    protected UserRankValidate $userRankValidate;

    public function __construct(UserRank $userRankModel)
    {
        $this->userRankModel = $userRankModel;
    }

    /**
     * 获取筛选结果
     *
     * @param array $filter
     * @return array
     */
    public function getFilterResult(array $filter): array
    {
        $query = $this->filterQuery($filter)->append(["rank_type_name"]);
        if (isset($filter['is_page']) && $filter["is_page"]) {
            $result = $query->field(["rank_id", "rank_name"])->select();
        } else {
            $result = $query->page($filter['page'], $filter['size'])->select();
        }

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
    protected function filterQuery(array $filter): object
    {
        $query = $this->userRankModel->query();
        // 处理筛选条件

        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->where('rank_name', 'like', '%' . $filter['keyword'] . '%');
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
     * @return array
     * @throws ApiException
     */
    public function getDetail(int $id): array
    {
        $result = $this->userRankModel->where('rank_id', $id)->append(["rank_type_name"])->find();

        if (!$result) {
            throw new ApiException('会员等级不存在');
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
        return $this->userRankModel::where('rank_id', $id)->value('rank_name');
    }

    /**
     * 执行会员等级添加或更新
     *
     * @param int $id
     * @param array $data
     * @param bool $isAdd
     * @return int|bool
     * @throws ApiException
     */
    public function updateUserRank(int $id, array $data, bool $isAdd = false)
    {
        validate(UserRankValidate::class)->only(array_keys($data))->check($data);

        // 非特殊会员组检查积分的上下限是否合理
        if ($data["rank_type"] == 2 && $data["min_growth_points"] >= $data["max_growth_points"]) {
            throw new ApiException('最小成长值不能大于等于最大成长值');
        }

        //特殊等级会员组不判断积分限制
        if ($data["rank_type"] == 1) {
            //检查上限/下限制有无重复
            if ($isAdd) {
                $min_points = UserRank::where(['min_growth_points' => $data['min_growth_points'], "rank_type" => 1])->find();
                $max_points = UserRank::where(['max_growth_points' => $data['max_growth_points'], "rank_type" => 1])->find();
            } else {
                $min_points = UserRank::where(['min_growth_points' => $data['min_growth_points'], "rank_type" => 1])->where("rank_id", "<>", $id)->find();
                $max_points = UserRank::where(['max_growth_points' => $data['max_growth_points'], "rank_type" => 1])->where("rank_id", "<>", $id)->find();
            }
            if ($min_points) {
                throw new ApiException('已经存在一个等级积分下限为 ' . $data['min_growth_points'] . ' 的会员等级');
            }
            if ($max_points) {
                throw new ApiException('已经存在一个等级积分上限为 ' . $data['max_growth_points'] . ' 的会员等级');
            }
        }

        if ($isAdd) {
            $result = $this->userRankModel->save($data);
            AdminLog::add('新增会员等级:' . $data['rank_name']);
            return $this->userRankModel->getKey();
        } else {
            if (!$id) {
                throw new ApiException('#id错误');
            }
            $result = $this->userRankModel->where('rank_id', $id)->save($data);
            AdminLog::add('更新会员等级:' . $this->getName($id));

            return $result !== false;
        }
    }

    /**
     * 更新单个字段
     *
     * @param int $id
     * @param array $data
     * @return int|bool
     * @throws ApiException
     */
    public function updateUserRankField(int $id, array $data)
    {
        validate(UserRankValidate::class)->only(array_keys($data))->check($data);

        $user_rank = $this->userRankModel::find($id)->toArray();
        list($user_rank_id, $field) = array_keys($data);

        // 非特殊会员组检查积分的上下限是否合理
        if ($user_rank["rank_type"] == 2) {
            if ($field == "min_growth_points" && $data["min_growth_points"] >= $user_rank["max_growth_points"]) {
                throw new ApiException('积分下限不能大于等于积分上限');
            } elseif ($field == "max_growth_points" && $data["max_growth_points"] <= $user_rank["min_growth_points"]) {
                throw new ApiException('积分上限不能小于等于积分下限');
            }
        }

        if (!$id) {
            throw new ApiException('#id错误');
        }
        $result = $this->userRankModel::where('rank_id', $id)->save($data);
        AdminLog::add('更新会员等级:' . $this->getName($id));
        return $result !== false;
    }

    /**
     * 删除会员等级
     *
     * @param int $id
     * @return bool
     */
    public function deleteUserRank(int $id): bool
    {
        if (!$id) {
            throw new ApiException('#id错误');
        }
        $get_name = $this->getName($id);
        $result = $this->userRankModel::destroy($id);

        if ($result) {
            AdminLog::add('删除会员等级:' . $get_name);
        }

        return $result !== false;
    }
    public function getUserRankList()
    {
        $result = $this->userRankModel->field('rank_id,rank_name,rank_type,discount')->order('min_growth_points')->select();
        return $result->toArray();
    }
}
