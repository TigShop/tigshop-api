<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 退换货
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\adminapi\controller\order;

use app\adminapi\AdminBaseController;
use app\service\api\admin\order\AftersalesService;
use exceptions\ApiException;
use think\App;
use think\response\Json;
use utils\Util;

/**
 * 退换货控制器
 */
class Aftersales extends AdminBaseController
{
    protected AftersalesService $aftersalesService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param AftersalesService $aftersalesService
     */
    public function __construct(App $app, AftersalesService $aftersalesService)
    {
        parent::__construct($app);
        $this->aftersalesService = $aftersalesService;
        $this->checkAuthor('aftersalesManage'); //权限检查
    }

    /**
     * 列表页面
     *
     * @return \think\Response
     */
    public function list(): \think\Response
    {
        $filter = $this->request->only([
            'keyword' => '',
            'page/d' => 1,
            'size/d' => 15,
            'sort_field' => 'aftersale_id',
            'sort_order' => 'desc',
            'status/d' => 0,
            'aftersale_type/d' => 0,
        ], 'get');

        $filterResult = $this->aftersalesService->getFilterResult($filter);
        $total = $this->aftersalesService->getFilterCount($filter);

        return $this->success([
            'filter_result' => $filterResult,
            'filter' => $filter,
            'total' => $total,
            'status_list' => \app\model\order\Aftersales::STATUS_NAME,
            'type_list' => \app\model\order\Aftersales::AFTERSALES_TYPE_NAME,
        ]);
    }

    /**
     * 详情接口
     *
     * @return \think\Response
     */
    public function detail(): \think\Response
    {
        $id = input('id/d', 0);
        $item = $this->aftersalesService->getDetail($id);
        $item['status_config'] = \app\model\order\Aftersales::STATUS_NAME;
        $item['aftersales_type_config'] = \app\model\order\Aftersales::AFTERSALES_TYPE_NAME;
        $item['refuse_reason'] = \app\model\order\Aftersales::REFUSE_REASON;
        $refundProductPrice = 0;
        foreach ($item['aftersales_items'] as $v) {
            $refundProductPrice += $v['number'] * $v['price'];
        }
        $item['suggest_refund_amount'] = Util::number_format_convert($refundProductPrice * $item['orders']['paid_amount'] / $item['orders']['product_amount']);
        return $this->success([
            'item' => $item,
        ]);
    }

    /**
     *
     * 同意或拒接售后接口
     * @return \think\Response
     */
    public function update(): \think\Response
    {
        $id = input('id/d', 0);
        $data = $this->request->only([
            'aftersale_id' => $id,
            'status/d' => 0,
            'reply' => '',
            'return_address' => '',
            'refund_amount' => 0,
        ], 'post');

        $result = $this->aftersalesService->agreeOrRefuse($id, $data);
        if ($result) {
            return $this->success('操作成功');
        } else {
            return $this->error('操作失败');
        }
    }

    /**
     *
     * 售后确认收货接口
     * @return \think\Response
     */
    public function receive(): \think\Response
    {
        $id = input('id/d', 0);
        $result = $this->aftersalesService->receive($id);
        if ($result) {
            return $this->success('操作成功');
        } else {
            return $this->error('更新失败');
        }
    }

    /**
     * 更新单个字段
     *
     * @return \think\Response
     */
    public function updateField(): \think\Response
    {
        $id = input('id/d', 0);
        $field = input('field', '');

        if (!in_array($field, ['aftersales_name', 'sort_order', 'is_show'])) {
            return $this->error('#field 错误');
        }

        $data = [
            'aftersale_id' => $id,
            $field => input('val'),
        ];

        $this->aftersalesService->updateAftersalesField($id, $data);

        return $this->success('更新成功');
    }

    /**
     * 删除
     *
     * @return Json
     */
//    public function del(): Json
//    {
//        $id = input('id/d', 0);
//        $this->aftersalesService->deleteAftersales($id);
//        return $this->success('指定项目已删除');
//    }

//    /**
//     * 批量操作
//     *
//     * @return Json
//     */
//    public function batch(): Json
//    {
//        if (empty(input('ids')) || !is_array(input('ids'))) {
//            return $this->error('未选择项目');
//        }
//
//        if (input('type') == 'del') {
//            foreach (input('ids') as $key => $id) {
//                $id = intval($id);
//                $this->aftersalesService->deleteAftersales($id);
//            }
//            return $this->success('批量操作执行成功！');
//        } else {
//            return $this->error('#type 错误');
//        }
//    }

    /**
     * 提交售后反馈记录
     * @return \think\Response
     * @throws ApiException
     */
    public function record(): \think\Response
    {
        $input = $this->request->only([
            'aftersale_id/d' => 0,
            'log_info' => '',
            'return_pic' => [],
        ]);
        $id = $input['aftersale_id'];
        unset($input['aftersale_id']);
        $result = $this->aftersalesService->submitFeedbackRecord($id, $input, request()->adminUid);
        if ($result) {
            return $this->success('操作成功');
        } else {
            return $this->error('更新失败');
        }
    }
}
