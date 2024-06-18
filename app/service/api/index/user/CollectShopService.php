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

namespace app\service\api\index\user;

use app\model\merchant\Shop;
use app\model\user\CollectShop;
use app\service\core\BaseService;
use exceptions\ApiException;
use log\AdminLog;

/**
 * 收藏店铺服务类
 */
class CollectShopService extends BaseService
{

    public function __construct(CollectShop $collectShop)
    {
        $this->model = $collectShop;
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


        if (isset($filter['user_id']) && $filter['user_id'] > 0) {
            $query->where('user_id', $filter['user_id']);
        }

        if (isset($filter['sort_field'], $filter['sort_order']) && !empty($filter['sort_field']) && !empty($filter['sort_order'])) {
            $query->order($filter['sort_field'], $filter['sort_order']);
        }
        return $query;
    }


    /**
     * 获取详情
     *
     * @param array $where
     * @return CollectShop
     */
    public function getDetail(array $where): CollectShop|null
    {
        return $this->model->where($where)->find();
    }


    /**
     * 创建收藏
     * @param array $data
     * @return Shop|\think\Model
     */
    public function create(array $data): Shop|\think\Model
    {
        $result = $this->model->create($data);
        return $result;
    }


    /**
     * 取消收藏
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        if (!$id) {
            throw new ApiException('#id错误');
        }
        $result = $this->model->destroy($id);

        return $result !== false;
    }

}
