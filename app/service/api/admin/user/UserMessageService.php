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

namespace app\service\api\admin\user;

use app\model\user\UserMessage;
use app\service\api\admin\BaseService;
use exceptions\ApiException;

/**
 * 用户站内信服务类
 */
class UserMessageService extends BaseService
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
        $query = $this->filterQuery($filter)->append(["add_time_date", 'add_time_hms']);
        $result = $query->page($filter['page'], $filter['size'])->order('message_id desc')->select();
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
    public function filterQuery(array $filter): Object
    {
        $query = UserMessage::query();

        if (isset($filter["unread"]) && !empty($filter['unread'])) {
            $query->where('is_read', 0);
        }
        if (request()->userId > 0) {
            $query->where('user_id', request()->userId);
        }
        return $query;
    }

    /**
     * 设置站内信已读（指定id已读）
     * @param int $id
     * @param int $user_id
     * @return bool
     * @throws ApiException
     */
    public function updateUserMessageToRead(int $id, int $user_id): bool
    {
        $query = UserMessage::query();
        $userMessage = $query->where('is_read', 0)->where('user_id', $user_id)->where('message_id', $id)->find();
        if ($userMessage) {
            $userMessage->is_read = 1;
            $userMessage->save();
        }
        return true;
    }

    /**
     * 设置站内信已读（全部设置已读）
     * @param int $id
     * @param int $user_id
     * @return bool
     * @throws ApiException
     */
    public function updateUserMessageToAllRead(int $user_id): bool
    {
        $query = UserMessage::query();
        $query->where('is_read', 0)->where('user_id', $user_id)->update(['is_read' => 1]);
        return true;
    }

    /**
     * 删除站内信
     * @param int $id
     * @param int $user_id
     * @return bool
     * @throws ApiException
     */
    public function deleteUserMessage(int $id, int $user_id): bool
    {
        if (!$id) {
            throw new ApiException('#id错误');
        }
        $message = UserMessage::find($id);
        if (!$message || $message['user_id'] != $user_id) {
            throw new ApiException('非法请求');
        }
        $result = UserMessage::destroy($id);

        return $result !== false;
    }
}
