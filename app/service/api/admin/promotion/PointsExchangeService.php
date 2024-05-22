<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 积分商品
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\promotion;

use app\common\exceptions\ApiException;
use app\common\utils\Util;
use app\model\promotion\PointsExchange;
use app\service\api\admin\BaseService;

/**
 * 积分商品服务类
 */
class PointsExchangeService extends BaseService
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
        $query = $this->filterQuery($filter)->with(['product']);
        $result = $query->page($filter['page'], $filter['size'])->select();
        // 积分价
        foreach ($result as $item) {
            $item->discounts_price = ($item->market_price - $item->points_deducted_amount) > 0 ? $item->market_price - $item->points_deducted_amount : 0;
            $item->discounts_price = Util::number_format_convert($item->discounts_price);
        }

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
        $query = PointsExchange::query();
        // 处理筛选条件

        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->productName($filter['keyword']);
        }

        if (isset($filter['is_enabled']) && $filter["is_enabled"] != -1) {
            $query->where('is_enabled', $filter['is_enabled']);
        }

        if (isset($filter['is_hot']) && $filter['is_hot'] != -1) {
            $query->isHot($filter['is_hot']);
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
     * @return PointsExchange
     * @throws ApiException
     */
    public function getDetail(int $id): PointsExchange
    {
        $result = PointsExchange::with(['product'])->find($id);

        if (!$result) {
            throw new ApiException(/** LANG */'积分商品不存在');
        }
        $result->discounts_price = ($result->market_price - $result->points_deducted_amount) > 0 ? $result->market_price - $result->points_deducted_amount : 0;
        $result->discounts_price = Util::number_format_convert($result->discounts_price);
        return $result;
    }

    /**
     * 添加积分商品
     * @param array $data
     * @return int
     */
    public function createPointsExchange(array $data): int
    {
        $result = PointsExchange::create($data);
        return $result->getKey();
    }

    /**
     * 执行积分商品更新
     * @param int $id
     * @param array $data
     * @return bool
     * @throws ApiException
     */
    public function updatePointsExchange(int $id, array $data): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = PointsExchange::where('id', $id)->save($data);
        return $result !== false;
    }

    /**
     * 更新单个字段
     *
     * @param int $id
     * @param array $data
     * @return int|bool
     * @throws ApiException
     */
    public function updatePointsExchangeField(int $id, array $data)
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = PointsExchange::where('id', $id)->save($data);
        return $result !== false;
    }

    /**
     * 删除积分商品
     *
     * @param int $id
     * @return bool
     */
    public function deletePointsExchange(int $id): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = PointsExchange::destroy($id);
        return $result !== false;
    }
}
