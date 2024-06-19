<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 店铺
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\merchant;

use app\model\merchant\Shop;
use app\model\merchant\ShopAccountLog;
use app\service\core\BaseService;
use think\Db;

/**
 * 店铺资金服务类
 */
class ShopAccountLogService extends BaseService
{

    public function __construct(ShopAccountLog $shopAccountLog)
    {
        $this->model = $shopAccountLog;
    }




    /**
     * 筛选查询
     *
     * @param array $filter
     * @return object
     */
    protected function filterQuery(array $filter): object
    {
        $query = $this->model->query();
        // 处理筛选条件

        if (isset($filter['shop_id']) && $filter['shop_id'] > -1) {
            $query->where('shop_id', $filter['shop_id']);
        }

        if (isset($filter['sort_field'], $filter['sort_order']) && !empty($filter['sort_field']) && !empty($filter['sort_order'])) {
            $query->order($filter['sort_field'], $filter['sort_order']);
        }
        return $query;
    }


    /**
     * 创建
     * @param array $data
     * @return Shop|\think\Model
     */
    public function create(array $data): Shop|\think\Model
    {
        $result = $this->model->create($data);
        return $result;
    }

    /**
     * @return void
     */
    public function addWithDrawLog($data)
    {
        $shop = Shop::where('shop_id', $data['shop_id'])->dec('shop_money', $data['amount'])->update();
        $new_shop_money = bcsub($shop['shop_money'], $data['amount'], 2);
        $this->create([
            'shop_money' => $shop['shop_money'],
            'frozen_money' => $shop['frozen_money'],
            'new_shop_money' => $new_shop_money,
            'new_frozen_money' => $shop['frozen_money'],
            'shop_id' => $data['shop_id'],
            'type' => 1,
        ]);

    }


}
