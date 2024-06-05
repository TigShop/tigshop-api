<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 优惠活动
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\promotion;

use app\model\promotion\ProductPromotion;
use app\service\core\BaseService;
use exceptions\ApiException;

/**
 * 优惠活动服务类
 */
class ProductPromotionService extends BaseService
{
    public function __construct()
    {
    }

    /**
     * 获取筛选结果
     *
     * @param array $filter
     * @return array
     */
    public function getFilterResult(array $filter): array
    {
        $query = $this->filterQuery($filter)->append(["promotion_type_name", 'product_status', 'product_time']);
        $result = $query->page($filter['page'], $filter['size'])->select();
        return $result->toArray();
    }

    /**
     * 获取筛选结果数量
     *
     * @param array $filter
     * @return int
     */
    public function getFilterCount(array $filter): int
    {
        $query = $this->filterQuery($filter);
        $count = $query->count();
        return $count;
    }

    /**
     * 筛选查询
     *
     * @param array $filter
     * @return object
     */
    public function filterQuery(array $filter): object
    {
        $query = ProductPromotion::query();
        // 处理筛选条件

        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->where('promotion_name', 'like', '%' . $filter['keyword'] . '%');
        }

        // 活动状态检索
        if (isset($filter['is_going']) && !empty($filter['is_going'])) {
            $query->productStatus($filter['is_going']);
        }

        if (isset($filter['sort_field'], $filter['sort_order']) && !empty($filter['sort_field']) && !empty($filter['sort_order'])) {
            $query->order($filter['sort_field'], $filter['sort_order']);
        }
        return $query;
    }

    /**
     * 获取详情
     *
     * @param int $id
     * @return ProductPromotion
     * @throws ApiException
     */
    public function getDetail(int $id): ProductPromotion
    {
        $result = ProductPromotion::where('promotion_id', $id)->append(["promotion_type_name", 'product_status', 'product_time'])->find();

        if (!$result) {
            throw new ApiException(/** LANG */'优惠活动不存在');
        }

        return $result;
    }

    /**
     * 添加优惠活动
     * @param array $data
     * @return int
     */
    public function createProductPromotion(array $data): int
    {
        // 数据处理
        if (!empty($data['product_time'])) {
            // 使用日期
            list($start, $end) = $data['product_time'];
            $data['start_time'] = strtotime($start);
            $data['end_time'] = strtotime($end);
        }
        unset($data['product_time']);
        $result = ProductPromotion::create($data);
        return $result->getKey();
    }


    /**
     * 执行优惠活动更新
     *
     * @param int $id
     * @param array $data
     * @return bool
     * @throws ApiException
     */
    public function updateProductPromotion(int $id, array $data): bool
    {
        // 数据处理
        if (!empty($data['product_time'])) {
            // 使用日期
            list($start, $end) = $data['product_time'];
            $data['start_time'] = strtotime($start);
            $data['end_time'] = strtotime($end);
        }

        unset($data['product_time']);

        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = ProductPromotion::where('promotion_id', $id)->save($data);
        return $result !== false;
    }

    /**
     * 更新单个字段
     * @param int $id
     * @param array $data
     * @return bool
     * @throws ApiException
     */
    public function updateProductPromotionField(int $id, array $data): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = ProductPromotion::where('promotion_id', $id)->save($data);
        return $result !== false;
    }

    /**
     * 删除优惠活动
     * @param int $id
     * @return bool
     */
    public function deleteProductPromotion(int $id): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = ProductPromotion::destroy($id);
        return $result !== false;
    }
}
