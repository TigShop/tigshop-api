<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 订单商品
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\order;

use app\model\order\OrderItem;
use app\service\api\admin\product\ProductService;
use app\service\core\BaseService;
use app\validate\order\OrderItemValidate;
use exceptions\ApiException;

/**
 * 订单商品服务类
 */
class OrderItemService extends BaseService
{
    protected OrderItem $orderItemModel;
    protected OrderItemValidate $orderItemValidate;

    public function __construct(OrderItem $orderItemModel)
    {
        $this->orderItemModel = $orderItemModel;
    }
    /**
     * 修改订单商品
     *
     * @param int $id
     * @param array $data
     * @throws ApiException
     */
    public function modifyOrderItem(int $id, array $data)
    {
        $orderDetailService = app(OrderDetailService::class)->setId($id);
        $arr = [];
        $item_ids = [];
        $product_amount = 0;
        foreach ($data as $key => $value) {
            if ($value['item_id'] > 0) {
                $item_ids[] = $value['item_id'];
            } else {
                unset($value['item_id']);
                $value['order_id'] = $id;
            }
            // 检查库存
            if (app(ProductService::class)->checkProductStock($value['quantity'], $value['product_id'], $value['sku_id']) == false) {
                throw new ApiException('商品:' . $value['product_name'] . '库存不足！');
            }
            $product_amount += $value['quantity'] * $value['price'];
            $arr[] = $value;
        }
        // 删除不在数组里的订单商品
        if ($item_ids) {
            $this->orderItemModel->where('order_id', $id)->whereNotIn('item_id', $item_ids)->delete();
        }
        $this->orderItemModel->saveAll($arr);
        // 重新计算订单金额
        $orderDetailService->updateOrderMoney();
    }

}
