<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 消息设置
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\admin\controller\setting;

use app\admin\AdminBaseController;
use app\service\api\admin\setting\MessageTypeService;
use exceptions\ApiException;
use think\App;
use think\facade\Db;
use think\Response;

/**
 * 消息设置控制器
 */
class MessageType extends AdminBaseController
{
    protected MessageTypeService $messageTypeService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param MessageTypeService $messageTypeService
     */
    public function __construct(App $app, MessageTypeService $messageTypeService)
    {
        parent::__construct($app);
        $this->messageTypeService = $messageTypeService;
        $this->checkAuthor('messageTypeManage'); //权限检查
    }

    /**
     * 列表页面
     * @return Response
     */
    public function list(): Response
    {
        $filter = $this->request->only([
            'keyword' => '',
            'send_type/d' => 0,
            'page/d' => 1,
            'size/d' => 15,
            'sort_field' => 'message_id',
            'sort_order' => 'desc',
        ], 'get');

        $filterResult = $this->messageTypeService->getFilterResult($filter);
        $total = $this->messageTypeService->getFilterCount($filter);

        return $this->success([
            'filter_result' => $filterResult,
            'filter' => $filter,
            'total' => $total,
        ]);
    }

    /**
     * 详情
     * @return Response
     */
    public function detail(): Response
    {
        $id = input('id/d', 0);
        $item = $this->messageTypeService->getDetail($id);
        return $this->success([
            'item' => $item,
        ]);
    }

    /**
     * 获取请求数据
     * @return array
     */
    public function requestData(): array
    {
        $data = $this->request->only([
            'describe' => '',
            'is_wechat' => '',
            'is_mini_program' => '',
            'is_message' => '',
            'is_msg' => '',
            'is_app' => '',
            'is_ding' => '',
            'template_message' => [],
        ], 'post');
        return $data;
    }

    /**
     * 添加
     * @return Response
     */
    public function create(): Response
    {
        $data = $this->requestData();
        $result = $this->messageTypeService->createMessageType($data);
        if ($result) {
            return $this->success(/** LANG */'消息设置添加成功');
        } else {
            return $this->error(/** LANG */'消息设置添加失败');
        }
    }

    /**
     * 执行更新操作
     * @return Response
     */
    public function update(): Response
    {
        $id = input('id/d', 0);
        $data = $this->requestData();
        $data["message_id"] = $id;
        $result = $this->messageTypeService->updateMessageType($id, $data);
        if ($result) {
            return $this->success(/** LANG */'消息设置更新成功');
        } else {
            return $this->error(/** LANG */'消息设置更新失败');
        }
    }

    /**
     * 更新单个字段
     *
     * @return Response
     */
    public function updateField(): Response
    {
        $id = input('id/d', 0);
        $field = input('field', '');

        if (!in_array($field,
            ['name', 'is_wechat', 'is_mini_program', 'is_message', 'is_msg', 'is_app', 'is_ding', 'add_time'])) {
            return $this->error(/** LANG */'#field 错误');
        }

        $data = [
            'message_id' => $id,
            $field => input('val'),
        ];

        $this->messageTypeService->updateMessageTypeField($id, $data);

        return $this->success(/** LANG */'更新成功');
    }

    /**
     * 删除
     *
     * @return Response
     */
    public function del(): Response
    {
        $id = input('id/d', 0);
        $this->messageTypeService->deleteMessageType($id);
        return $this->success(/** LANG */'指定项目已删除');
    }

    /**
     * 批量操作
     *
     * @return Response
     */
    public function batch(): Response
    {
        if (empty(input('ids')) || !is_array(input('ids'))) {
            return $this->error(/** LANG */'未选择项目');
        }

        if (input('type') == 'del') {
            try {
                //批量操作一定要事务
                Db::startTrans();
                foreach (input('ids') as $key => $id) {
                    $id = intval($id);
                    $this->messageTypeService->deleteMessageType($id);
                }
                Db::commit();
            } catch (\Exception $exception) {
                Db::rollback();
                throw new ApiException($exception->getMessage());
            }

            return $this->success(/** LANG */'批量操作执行成功！');
        } else {
            return $this->error(/** LANG */'#type 错误');
        }
    }

    /**
     * 生成小程序消息模板
     * @return \think\Response
     * @throws \exceptions\ApiException
     */
    public function miniProgramMessageTemplate()
    {
        $this->messageTypeService->generateMiniProgramMessageTemplate();
        return $this->success(/** LANG */'消息模板更新成功');
    }

    /**
     * 同步小程序消息模板
     * @return \think\Response
     * @throws \exceptions\ApiException
     */
    public function miniProgramMessageTemplateSync()
    {
        $this->messageTypeService->generateMiniProgramMessageTemplateSync();
        return $this->success(/** LANG */'消息模板同步成功');
    }
}
