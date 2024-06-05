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

use app\model\setting\MessageTemplate;
use app\model\setting\MessageType;
use app\service\api\admin\oauth\WechatOAuthService;
use app\service\core\BaseService;
use exceptions\ApiException;
use log\AdminLog;
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
            throw new ApiException(/** LANG */ '消息设置不存在');
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
                    $templateMessageInfo[$i] = (object)[];
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
            throw new ApiException(/** LANG */ '#id错误');
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
            throw new ApiException(/** LANG */ '#id错误');
        }
        $get_name = $this->getName($id);
        $result = MessageType::destroy($id);

        if ($result) {
            AdminLog::add('删除消息设置:' . $get_name);
        }

        return $result !== false;
    }

    /**
     * 生成公众号模板
     * @return bool
     * @throws ApiException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function generateWechatMessageTemplate(): bool
    {
        // 获取token
        $app = new WechatOAuthService();
        $access_token = $app->setPlatformType('wechat')->getApplication()->getAccessToken()->getToken();
        //删除指定模板
        $url = "https://api.weixin.qq.com/cgi-bin/template/get_all_private_template?access_token=" . $access_token;
        $result = $app->getApplication()->getClient()->get($url);
        $res = $result->toArray();
        if (isset($res['template_list'])) {
            $template_list = $res['template_list'];
            $url = "https://api.weixin.qq.com/cgi-bin/template/del_private_template?access_token=" . $access_token;
            foreach ($template_list as $k => $v) {
                $data = ['template_id' => $v['template_id']];
                $app->getApplication()->getClient()->postJson($url, $data);
            }
        }
        //添加模板
        $url = 'https://api.weixin.qq.com/wxaapi/newtmpl/addtemplate?access_token=' . $access_token;
        $data = [
            'tid' => "51617",
            "kidList" => [3, 7, 4],
            'sceneDesc' => '订单支付成功通知',
        ];
        $res = $app->getApplication()->getClient()->postJson($url, $data);
        if (!$res['priTmplId']) {
            throw new ApiException($res['errmsg']);
        }
        $data = [
            'tid' => "48233",
            "kidList" => [21, 18, 3, 17, 2],
            'sceneDesc' => '订单发货通知',
        ];
        $res = $app->getApplication()->getClient()->postJson($url, $data);
        if (!$res['priTmplId']) {
            throw new ApiException($res['errmsg']);
        }
        $data = [
            'tid' => "48058",
            "kidList" => [5, 2],
            'sceneDesc' => '退款成功通知',
        ];
        $res = $app->getApplication()->getClient()->postJson($url, $data);
        if (!$res['priTmplId']) {
            throw new ApiException($res['errmsg']);
        }

        return true;
    }

    /**
     * 生成小程序消息模板
     * @return bool
     * @throws ApiException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function generateMiniProgramMessageTemplate(): bool
    {
        // 获取token
        $app = new WechatOAuthService();
        $access_token = $app->setPlatformType('miniProgram')->getApplication()->getAccessToken()->getToken();
        //删除指定模板
        $url = "https://api.weixin.qq.com/wxaapi/newtmpl/gettemplate?access_token=" . $access_token;
        $res = $app->getApplication()->getClient()->get($url);
        if (isset($res['data']) && $res['errmsg'] == 'ok' && $res['errcode'] == 0) {
            $template_list = $res['data'];
            $url = "https://api.weixin.qq.com/wxaapi/newtmpl/deltemplate?access_token=" . $access_token;
            foreach ($template_list as $k => $v) {
                if ($v['title'] == '订单支付通知' || $v['title'] == '订单发货通知') {
                    $data = ['priTmplId' => $v['priTmplId']];
                    $app->getApplication()->getClient()->postJson($url, $data);
                }
            }
        }

        //添加模板
        $url = 'https://api.weixin.qq.com/wxaapi/newtmpl/addtemplate?access_token=' . $access_token;
        $data = [
            'tid' => "31570",
            "kidList" => [2, 1, 4],
            'sceneDesc' => '订单支付成功通知',
        ];
        $res = $app->getApplication()->getClient()->postJson($url, $data);
        if (!$res['priTmplId']) {
            throw new ApiException($res['errmsg']);
        }
        $data = [
            'tid' => "30766",
            "kidList" => [4, 5, 3, 8, 2],
            'sceneDesc' => '订单发货通知',
        ];
        $res = $app->getApplication()->getClient()->postJson($url, $data);
        if (!$res['priTmplId']) {
            throw new ApiException($res['errmsg']);
        }

        return true;
    }

    /**
     * 同步小程序消息模板
     * @return bool
     * @throws ApiException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function generateMiniProgramMessageTemplateSync(): bool
    {
        // 获取token
        $app = new WechatOAuthService();
        $access_token = $app->setPlatformType('miniProgram')->getApplication()->getAccessToken()->getToken();
        $url = "https://api.weixin.qq.com/wxaapi/newtmpl/gettemplate?access_token=" . $access_token;
        $res = $app->getApplication()->getClient()->get($url);
        // 修改模板
        if (isset($res['data']) && $res['errmsg'] == 'ok' && $res['errcode'] == 0) {
            $template_list = $res['data'];
            foreach ($template_list as $k => $v) {
                //重置本地模板列表
                if ($v['title'] == '订单支付通知') {
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
     * 同步小程序消息模板
     * @return bool
     * @throws ApiException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function generateWechatMessageTemplateSync(): bool
    {
        // 获取token
        $app = new WechatOAuthService();
        $access_token = $app->setPlatformType('wechat')->getApplication()->getAccessToken()->getToken();
        $url = "https://api.weixin.qq.com/cgi-bin/template/get_all_private_template?access_token=" . $access_token;
        $result = $app->getApplication()->getClient()->get($url);
        $res = $result->toArray();
        // 修改模板
        if (isset($res['template_list'])) {
            $template_list = $res['template_list'];
            foreach ($template_list as $k => $v) {
                //重置本地模板列表
                if ($v['title'] == '订单支付成功提醒') {
                    $data = [
                        'template_id' => $v['template_id'],
                        'content' => $v['content'],
                    ];
                    MessageTemplate::where(['message_id' => 2, 'type' => 1])->update($data);
                }
                if ($v['title'] == '订单发货通知') {
                    $data = [
                        'template_id' => $v['template_id'],
                        'content' => $v['content'],
                    ];
                    MessageTemplate::where(['message_id' => 3, 'type' => 1])->update($data);
                }
                if ($v['title'] == '退款成功通知') {
                    $data = [
                        'template_id' => $v['template_id'],
                        'content' => $v['content'],
                    ];
                    MessageTemplate::where(['message_id' => 4, 'type' => 1])->update($data);
                }
            }
            return true;
        } else {
            throw new ApiException($res['errmsg']);
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
    public function updateMessageTypeField(int $id, array $data)
    {
        if (!$id) {
            throw new ApiException(/** LANG */ '#id错误');
        }
        $result = MessageType::where('message_id', $id)->save($data);
        AdminLog::add('更新消息设置:' . $this->getName($id));
        return $result !== false;
    }
}
