<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 消息设置
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\setting;

use app\common\exceptions\ApiException;
use app\common\log\AdminLog;
use app\model\setting\MessageTemplate;
use app\model\setting\MessageType;
use app\service\api\admin\BaseService;
use app\service\api\admin\oauth\MiniWechatService;
use think\Exception;
use think\facade\Db;

/**
 * 消息设置服务类
 */
class MessageTypeService extends BaseService
{
    /**
     * 获取筛选结果
     *
     * @param array $filter
     * @return array
     */
    public function getFilterResult(array $filter): array
    {
        $query = $this->filterQuery($filter);
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
        $query = MessageType::query();
        // 处理筛选条件
        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->where('name', 'like', '%' . $filter['keyword'] . '%');
        }

        if (isset($filter['send_type']) && !empty($filter['send_type'])) {
            $query->where('send_type', $filter['send_type']);
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
     * @return MessageType
     * @throws ApiException
     */
    public function getDetail(int $id): MessageType
    {
        $result = MessageType::with(['template_message'])->where('message_id', $id)->find();
        if (!$result) {
            throw new ApiException(/** LANG */'消息设置不存在');
        }

        $templateMessageInfo = $templateMessage = [];

        // 封装消息模板数据
        if (!empty($result["template_message"])) {
            foreach ($result["template_message"] as $item) {
                $type = $item['type'];
                $templateMessageInfo[$type] = $item;
            }
            for ($i = 1; $i <= 6; $i++) {
                if (!isset($templateMessageInfo[$i])) {
                    $templateMessageInfo[$i] = (object) [];
                }
                switch ($i) {
                    case 1:
                        $templateMessage["wechat_data"] = $templateMessageInfo[$i];
                        break;
                    case 2:
                        $templateMessage["mini_program_data"] = $templateMessageInfo[$i];
                        break;
                    case 3:
                        $templateMessage["msg_data"] = $templateMessageInfo[$i];
                        break;
                    case 4:
                        $templateMessage["message_data"] = $templateMessageInfo[$i];
                        break;
                    case 5:
                        $templateMessage["app_data"] = $templateMessageInfo[$i];
                        break;
                    case 6:
                        $templateMessage["ding_data"] = $templateMessageInfo[$i];
                }
            }
            $result["template_message"] = $templateMessage;
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
        return MessageType::where('message_id', $id)->value('name');
    }

    /**
     * 添加消息设置
     * @param array $data
     * @return int
     */
    public function createMessageType(array $data): int
    {
        $result = MessageType::create($data);
        AdminLog::add('新增消息设置:' . $data['name']);
        return $result->getKey();
    }

    /**
     * 执行消息设置更新
     * @param int $id
     * @param array $data
     * @return bool
     * @throws ApiException
     */
    public function updateMessageType(int $id, array $data): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $info = MessageType::find($id)->toArray();

        //站内信设置项
        $message_type_data = [];
        if ($info['is_message'] > -1) {
            $message_type_data['is_message'] = $data["is_message"];
        }

        if ($info['is_msg'] > -1) {
            $message_type_data['is_msg'] = $data["is_msg"];
        }

        if ($info['is_wechat'] > -1) {
            $message_type_data['is_wechat'] = $data["is_wechat"];
        }

        if ($info['is_mini_program'] > -1) {
            $message_type_data['is_mini_program'] = $data["is_mini_program"];
        }

        if ($info['is_app'] > -1) {
            $message_type_data['is_app'] = $data["is_app"];
        }

        if ($info['is_ding'] > -1) {
            $message_type_data['is_ding'] = $data["is_ding"];
        }

        $template_message = $data['template_message'];
        $message_data = [
            'template_name' => $template_message['message_data']['template_name'] ?? '',
            'content' => $template_message['message_data']['content'] ?? '',
        ];

        //短信设置项
        $msg_data = [
            'template_id' => $template_message['msg_data']['template_id'] ?? '',
        ];
        //公众号设置项
        $wechat_data = [
            'template_id' => $template_message['wechat_data']['template_id'] ?? '',
        ];
        //小程序设置项
        $min_program_data = [
            'template_id' => $template_message['mini_program_data']['template_id'] ?? '',
        ];
        //app设置项
        $app_data = [
            'template_name' => $template_message['app_data']['template_name'] ?? '',
            'content' => $template_message['app_data']['content'] ?? '',
        ];

        //钉钉设置项
        $ding_data = [
//                'to_userid' => implode(',', $template_message['ding_data']['template_name']),
            'content' => $template_message['ding_data']['content'] ?? '',

        ];

        // 开启事务
        Db::startTrans();
        try {
            $result = MessageType::where('message_id', $id)->save($message_type_data);
            if ($info['is_message'] > -1) {
                MessageTemplate::where(['message_id' => $id, "type" => 4])->save($message_data);
            }
            if ($info['is_msg'] > -1) {
                MessageTemplate::where(['message_id' => $id, "type" => 3])->save($msg_data);
            }
            if ($info['is_wechat'] > -1) {
                MessageTemplate::where(['message_id' => $id, "type" => 1])->save($wechat_data);
            }
            if ($info['is_mini_program'] > -1) {
                MessageTemplate::where(['message_id' => $id, "type" => 2])->save($min_program_data);
            }
            if ($info['is_app'] > -1) {
                MessageTemplate::where(['message_id' => $id, "type" => 5])->save($app_data);
            }

            if ($info['is_ding'] > -1) {
                MessageTemplate::where(['message_id' => $id, "type" => 6])->save($ding_data);
            }
            Db::commit();
            AdminLog::add('更新消息设置:' . $this->getName($id));
            return $result !== false;
        } catch (Exception $e) {
            Db::rollback();
            throw new ApiException($e->getMessage());
        }
    }

    /**
     * 删除消息设置
     *
     * @param int $id
     * @return bool
     */
    public function deleteMessageType(int $id): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $get_name = $this->getName($id);
        $result = MessageType::destroy($id);

        if ($result) {
            AdminLog::add('删除消息设置:' . $get_name);
        }

        return $result !== false;
    }

    /**
     * 生成小程序消息模板
     * @return true
     * @throws ApiException
     */
    public function generateMiniProgramMessageTemplate()
    {
        // 获取token
        $access_token = $this->getWxAccessToken();
        //删除指定模板
        $url = "https://api.weixin.qq.com/wxaapi/newtmpl/gettemplate?access_token=" . $access_token;
        $res = $this->getMiniApplication()->getClient()->get($url);
        if (isset($res['data']) && $res['errmsg'] == 'ok' && $res['errcode'] == 0) {
            $template_list = $res['data'];
            $url = "https://api.weixin.qq.com/wxaapi/newtmpl/deltemplate?access_token=" . $access_token;
            foreach ($template_list as $k => $v) {
                if ($v['title'] == '支付成功通知' || $v['title'] == '订单发货通知') {
                    $data = ['priTmplId' => $v['priTmplId']];
                    $this->getMiniApplication()->getClient()->postJson($url, $data);
                }
            }
        }

        //添加模板
        $url = 'https://api.weixin.qq.com/wxaapi/newtmpl/addtemplate?access_token=' . $access_token;
        $data = [
            'tid' => "1081",
            "kidList" => [2, 1, 8, 4, 5],
            'sceneDesc' => '订单支付成功通知',
        ];
        $res = $this->getMiniApplication()->getClient()->postJson($url, $data);
        if (!$res['priTmplId']) {
            throw new ApiException($res['errmsg']);
        }
        $data = [
            'tid' => "1138",
            "kidList" => [1, 2, 3, 4],
            'sceneDesc' => '订单发货通知',
        ];
        $res = $this->getMiniApplication()->getClient()->postJson($url, $data);
        if (!$res['priTmplId']) {
            throw new ApiException($res['errmsg']);
        }

        return true;
    }

    /**
     * 同步小程序消息模板
     * @return true
     * @throws ApiException
     */
    public function generateMiniProgramMessageTemplateSync()
    {
        // 获取token
        $access_token = $this->getWxAccessToken();
        $url = "https://api.weixin.qq.com/wxaapi/newtmpl/gettemplate?access_token=" . $access_token;
        $res = $this->getMiniApplication()->getClient()->get($url);
        // 修改模板
        if (isset($res['data']) && $res['errmsg'] == 'ok' && $res['errcode'] == 0) {
            $template_list = $res['data'];
            foreach ($template_list as $k => $v) {
                //重置本地模板列表
                if ($v['title'] == '支付成功通知') {
                    $data = [
                        'template_id' => $v['priTmplId'],
                        'content' => $v['content'],
                    ];
                    MessageTemplate::where(['message_id' => 2, 'type' => 2])->update($data);
                }
                if ($v['title'] == '订单发货通知') {
                    $data = [
                        'template_id' => $v['priTmplId'],
                        'content' => $v['content'],
                    ];
                    MessageTemplate::where(['message_id' => 3, 'type' => 2])->update($data);
                }
            }
            return true;
        } else {
            throw new ApiException($res['errmsg']);
        }
    }

    /**
     * 获取小程序access_token
     * @return string
     * @throws ApiException
     */
    public function getWxAccessToken(): string
    {
        try {
            $app = $this->getMiniApplication();
            $access_token = $app->getAccessToken()->getToken();
        } catch (\Exception $exception) {
            throw new ApiException($exception->getMessage());
        }

        return $access_token;
    }

    public function getMiniApplication(): object
    {
        return app(MiniWechatService::class)->getApplication();
    }

    /**
     * 更新单个字段
     *
     * @param int $id
     * @param array $data
     * @return int|bool
     * @throws ApiException
     */
    public function updateMessageTypeField(int $id, array $data)
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = MessageType::where('message_id', $id)->save($data);
        AdminLog::add('更新消息设置:' . $this->getName($id));
        return $result !== false;
    }
}
