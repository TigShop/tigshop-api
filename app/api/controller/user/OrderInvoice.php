<?php
//**---------------------------------------------------------------------+
//** 通用接口控制器文件 -- 订单发票申请
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\api\controller\user;

use app\api\IndexBaseController;
use app\service\api\admin\finance\OrderInvoiceService;
use app\validate\finance\OrderInvoiceValidate;
use think\App;
use think\exception\ValidateException;
use think\Response;

class OrderInvoice extends IndexBaseController
{
    protected OrderInvoiceService $orderInvoiceService;
    protected $isAdd = false;

    /**
     * 构造函数
     * @param App $app
     * @param OrderInvoiceService $orderInvoiceService
     */
    public function __construct(App $app, OrderInvoiceService $orderInvoiceService)
    {
        parent::__construct($app);
        $this->orderInvoiceService = $orderInvoiceService;
    }

    /**
     * 添加更新订单发票
     * @return Response
     * @throws \exceptions\ApiException
     */
    public function update(): Response
    {
        $id = input('id/d', 0);
        $data = $this->request->only([
            "invoice_type/d" => 1,
            "status/d" => 0,
            "title_type/d" => 1,
            "company_code" => "",
            "company_name" => "",
            "company_address" => "",
            "company_phone" => "",
            "company_bank" => "",
            "company_account" => "",
            "invoice_content" => "商品明细",
            "amount" => "0.00",
            "mobile" => "",
            "email" => "",
        ], 'post');

        try {
            validate(OrderInvoiceValidate::class)
                ->scene('update')
                ->check($data);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        $result = $this->orderInvoiceService->updateOrderInvoicePc($id, request()->userId, $data, $this->isAdd);
        if ($result) {
            return $this->success($this->isAdd ? /** LANG */'发票申请添加成功' : /** LANG */'发票申请更新成功');
        } else {
            return $this->error(/** LANG */'发票申请更新失败');
        }
    }

    /**
     * 添加发票申请
     * @return Response
     * @throws \exceptions\ApiException
     */
    public function create(): Response
    {
        $this->isAdd = true;
        return $this->update();
    }

    /**
     * 订单发票详情
     * @return Response
     * @throws \exceptions\ApiException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function detail(): Response
    {
        $id = input('id/d', 0);
        $item = $this->orderInvoiceService->getOrderInvoiceDetail($id,request()->userId);
        return $this->success([
            'item' => $item,
        ]);
    }

}
