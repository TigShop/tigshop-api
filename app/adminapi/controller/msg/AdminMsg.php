<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 管理员消息
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\adminapi\controller\msg;

use app\adminapi\AdminBaseController;
use app\service\api\admin\msg\AdminMsgService;
use think\App;
use think\Response;

/**
 * 示例模板控制器
 */
class AdminMsg extends AdminBaseController
{
    /**
     * 构造函数
     *
     * @param App $app
     * @param AdminMsgService $AdminMsgService
     */
    public function __construct(App $app, protected AdminMsgService $AdminMsgService)
    {
        parent::__construct($app);
        $this->checkAuthor('adminMsg'); //权限检查
    }

    /**
     * 列表页面
     *
     * @return Response
     */
    public function list(): Response
    {
        $filter = $this->request->only([
            'keyword' => '',
            'msg_type' => 11,
            'page/d' => 1,
            'size/d' => 15,
            'sort_field' => ['is_readed' => 'asc', 'msg_id' => 'desc'],
            'sort_order' => '',
            'shop_id/d' => -2, // 店铺id
        ], 'get');
        if (request()->adminType == 'shop') {
            $filter['shop_id'] = $this->shopId;
        }
        $filterResult = $this->AdminMsgService->getFilterResult($filter);
        $total = $this->AdminMsgService->getFilterCount($filter);
        $msg_type_arr = $this->AdminMsgService->getMsgType();

        return $this->success([
            'filter_result' => $filterResult,
            'msg_type_arr' => $msg_type_arr,
            'filter' => $filter,
            'total' => $total,
        ]);
    }

    /**
     * 设置单个已读
     * @return Response
     * @throws \exceptions\ApiException
     */
    public function setReaded(): Response
    {
        $id = input('msg_id/d', 0);
        $this->AdminMsgService->setReaded($id);
        return $this->success('已设置为已读');
    }

    /**
     * 设置全部已读
     * @return Response
     */
    public function setAllReaded(): Response
    {
        $this->AdminMsgService->setAllReaded();
        return $this->success('已设置为已读');
    }

}
