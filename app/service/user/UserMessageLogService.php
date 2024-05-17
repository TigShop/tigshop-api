<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 站内信
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
use app\model\user\User;
use app\model\user\UserMessage;
use app\model\user\UserMessageLog;
use app\service\BaseService;

/**
 * 站内信服务类
 */
class UserMessageLogService extends BaseService
{

    public function __construct(protected UserMessageLog $userMessageLogModel, protected User $userModel, protected UserMessage $userMessageModel)
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
        $query = $this->filterQuery($filter)->append(['send_type_name', 'status_name']);
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
    protected function filterQuery(array $filter): object
    {
        $query = $this->userMessageLogModel->query();
        // 处理筛选条件
        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->where('message_title', 'like', '%' . $filter['keyword'] . '%');
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
        $result = $this->userMessageLogModel->where('message_log_id', $id)->find();

        if (!$result) {
            throw new ApiException('站内信不存在');
        }

        return $result->toArray();
    }

    /**
     * 执行发送站内信操作
     * @param int $id
     * @param array $data
     * @param bool $isAdd
     * @return bool
     * @throws ApiException
     */
    public function updateMessageLog(int $id, array $data, bool $isAdd = false)
    {
        if (!$isAdd) {
            return false;
        }
        $send_user_type = $data['send_user_type'];

        if ($send_user_type == 0) {
            $user_ids = $this->userModel->column('user_id');
            $user_ids = implode(',', $user_ids);
        } elseif ($send_user_type == 1) {
            $user_ids = $data['user_ids'];
        } elseif ($send_user_type == 2) {
            $user_rank = $data['user_rank'];
            $user_ids = $this->userModel->where('rank_id', $user_rank)->column('user_id');
            $user_ids = implode(',', $user_ids);
        } elseif ($send_user_type == 3) {
            $user_list = $data['user_ids'];
            $user_list = explode(',', $user_list);
            $user_ids = array();
            foreach ($user_list as $key => $row) {
                $user_ids_arr = $this->userModel->where('username', $row)->value('user_id');
                if (empty($user_ids_arr)) {
                    throw new ApiException('请检查输入的每个会员名称是否正确');
                } else {
                    $user_ids[] = $user_ids_arr;
                }
            }
            $user_ids = implode(',', $user_ids);

        }

        unset($data['user_list'], $data['user_id']);
        $data['user_ids'] = empty($user_ids) ? '' : $user_ids;
        $data['message_link'] = $data['message_link'] ? json_encode($data['message_link']) : '';
        if (empty($user_ids)) {
            throw new ApiException('该会员选择类型下没有会员');
        } else {
            $insert_id = $this->userMessageLogModel->insert($data, true);
        }
        if ($insert_id && ($send_user_type == 0 || $send_user_type == 1 || $send_user_type == 2 || $send_user_type == 3)) {

            /*插入前台数据表*/
            $user_ids = explode(',', $user_ids);

            foreach ($user_ids as $key => $id) {
                $user_message_data[] = [
                    'title' => $data['message_title'],
                    'content' => $data['message_content'],
                    'link' => $data['message_link'],
                    'message_log_id' => $insert_id,
                    'user_id' => $id,
                ];
            }
            $this->userMessageModel->saveAll($user_message_data);
        }

        return true;
    }

    /**
     * 删除站内信
     * @param int $id
     * @return bool
     * @throws ApiException
     */
    public function deleteMessageLog(int $id)
    {

        if (!$id) {
            throw new ApiException('#id错误');
        }
        $get_name = $this->getName($id);
        $result = $this->userMessageLogModel::destroy($id);

        if ($result) {
            AdminLog::add('删除站内信:' . $get_name);
        }

        return $result !== false;

    }

    /**
     * 获取名称
     *
     * @param int $id
     * @return string|null
     */
    public function getName(int $id): ?string
    {
        return $this->userMessageLogModel::where('message_log_id', $id)->value('message_title');
    }

    /**
     * 撤回
     * @param int $id
     * @return bool
     */
    public function recallMessageLog(int $id): bool
    {
        if (!$id) {
            throw new ApiException('#id错误');
        }

        $query1 = $this->userMessageModel::where('message_log_id', $id)->delete();

        $query2 = $this->userMessageLogModel::where('message_log_id', $id)->save(['is_recall' => 1]);

        return $query1 && $query2;
    }

}
