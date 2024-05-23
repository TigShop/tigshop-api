<?php
//**---------------------------------------------------------------------+
//** 通用接口控制器文件 -- 增票资质
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\index\controller\user;

use app\index\IndexBaseController;
use app\service\api\admin\finance\UserInvoiceService;
use think\App;
use think\Response;

class Invoice extends IndexBaseController
{
    protected UserInvoiceService $userInvoiceService;

    /**
     * 构造函数
     *
     * @param App $app
     */
    public function __construct(App $app, UserInvoiceService $userInvoiceService)
    {
        parent::__construct($app);
        $this->checkLogin();
        $this->userInvoiceService = $userInvoiceService;
    }

    /**
     * 请求数据
     * @return array
     */
    public function requestData(): array
    {
        $data = $this->request->only([
            "status/d" => 2,
            'company_name' => '',
            "company_code" => "",
            "company_address" => "",
            "company_phone" => "",
            "company_bank" => "",
            "company_account" => "",
            "is_confirm/d" => 0,
            'title_type/d' => 2,
            "invoice_type/d" => 1,
        ], 'post');
        return $data;
    }


    /**
     * 执行添加操作
     * @return Response
     * @throws \exceptions\ApiException
     * @throws \think\db\exception\DbException
     */
    public function create(): Response
    {
        $data = $this->requestData();
        $result = $this->userInvoiceService->createUserInvoice(request()->userId, $data);
        if ($result) {
            return $this->success(/** LANG */'您已成功提交增票资质，我们将在1-2个工作日内完成审核！');
        } else {
            return $this->error(/** LANG */'增票资质申请添加失败');
        }
    }

    /**
     * 执行更新操作
     * @return Response
     * @throws \exceptions\ApiException
     */
    public function update(): Response
    {
        $id = input('id/d', 0);
        $data = $this->requestData();
        $data["invoice_id"] = $id;

        $result = $this->userInvoiceService->updateUserInvoice($id, request()->userId, $data);
        if ($result) {
            return $this->success(/** LANG */'您已成功修改增票资质，我们将在1-2个工作日内重新审核！');
        } else {
            return $this->error(/** LANG */'增票资质申请更新失败');
        }
    }

    /**
     * 添加或编辑页面
     * @return Response
     * @throws \exceptions\ApiException
     */
    public function detail(): Response
    {
        $item = $this->userInvoiceService->getUserInvoiceDetail();
        return $this->success([
            'item' => $item,
        ]);
    }

    /**
     * 判断当前用户的增票资质是否审核通过
     * @return Response
     */
    public function getStatus(): Response
    {
        $item = $this->userInvoiceService->getInvoiceStatus(request()->userId);
        return $this->success([
            'item' => $item,
        ]);
    }
}
