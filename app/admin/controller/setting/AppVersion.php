<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- APP版本管理
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\admin\controller\setting;

use app\admin\AdminBaseController;
use app\service\api\admin\setting\AppVersionService;
use think\App;
use think\Response;

/**
 * APP版本管理控制器
 */
class AppVersion extends AdminBaseController
{
    protected AppVersionService $appVersionService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param AppVersionService $appVersionService
     */
    public function __construct(App $app, AppVersionService $appVersionService)
    {
        parent::__construct($app);
        $this->appVersionService = $appVersionService;
        $this->checkAuthor('appVersionManage'); //权限检查
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
            'page/d' => 1,
            'size/d' => 15,
            'sort_field' => 'id',
            'sort_order' => 'desc',
        ], 'get');

        $filterResult = $this->appVersionService->getFilterResult($filter);
        $total = $this->appVersionService->getFilterCount($filter);

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
        $item = $this->appVersionService->getDetail($id)["data"];
        return $this->success([
            'item' => $item,
        ]);
    }

    /**
     * 添加
     * @return Response
     * @throws \app\common\exceptions\ApiException
     */
    public function create(): Response
    {
        $data = $this->request->only([
            'ios_version' => '',
            'android_version' => '',
            'ios_link' => '',
            'android_link' => '',
            'hot_update_link' => '',
            'hot_update_type' => '',
        ], 'post');

        $result = $this->appVersionService->createAppVersion($data);
        if ($result) {
            return $this->success('APP版本管理添加成功');
        } else {
            return $this->error('APP版本管理添加失败');
        }
    }

    /**
     * 执行更新操作
     * @return Response
     */
    public function update(): Response
    {
        $id = input('id/d', 0);
        $data = $this->request->only([
            'id' => $id,
            'ios_version' => '',
            'android_version' => '',
            'ios_link' => '',
            'android_link' => '',
            'hot_update_link' => '',
            'hot_update_type' => '',
        ], 'post');

        $result = $this->appVersionService->updateAppVersion($id, $data);
        if ($result) {
            return $this->success('APP版本管理更新成功');
        } else {
            return $this->error('APP版本管理更新失败');
        }
    }
}
