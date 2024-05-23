<?php
//**---------------------------------------------------------------------+
//** 通用接口控制器文件 -- 站内信
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\index\controller\user;

use app\index\IndexBaseController;
use app\service\api\admin\user\UserMessageService;
use think\App;
use think\Response;

/**
 * 我的站内信控制器
 */
class Message extends IndexBaseController
{
    protected UserMessageService $userMessageService;
    /**
     * 构造函数
     *
     * @param App $app
     */
    public function __construct(App $app, UserMessageService $userMessageService)
    {
        parent::__construct($app);
        $this->checkLogin();
        $this->userMessageService = $userMessageService;
    }

    /**
     * 会员站内信列表
     * @return Response
     */
    public function list(): Response
    {
        $filter = $this->request->only([
            'page/d' => 1,
            'size/d' => 15,
            'unread/d' => 0,
        ], 'get');
        $filterResult = $this->userMessageService->getFilterResult($filter);
        $total = $this->userMessageService->getFilterCount($filter);
        return $this->success([
            'filter_result' => $filterResult,
            'filter' => $filter,
            'total' => $total,
        ]);
    }

    /**
     * 全部标记已读
     * @return Response
     * @throws \exceptions\ApiException
     */
    public function updateAllRead(): Response
    {
        $result = $this->userMessageService->updateUserMessageToAllRead(request()->userId);
        return $result ? $this->success(/** LANG */"添加成功") : $this->error(/** LANG */"添加失败");
    }

    /**
     * 设置站内信已读
     * @return Response
     * @throws \exceptions\ApiException
     */
    public function updateMessageRead(): Response
    {
        $id = input('id/d', 0);
        $result = $this->userMessageService->updateUserMessageToRead($id, request()->userId);
        return $result ? $this->success(/** LANG */"添加成功") : $this->error(/** LANG */"添加失败");
    }

    /**
     * 删除站内信
     * @return Response
     * @throws \exceptions\ApiException
     */
    public function del(): Response
    {
        $id = input('id/d', 0);
        $result = $this->userMessageService->deleteUserMessage($id, request()->userId);
        return $result ? $this->success(/** LANG */'删除成功') : $this->error(/** LANG */'删除失败');
    }

}
