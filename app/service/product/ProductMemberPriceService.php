<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 会员价格
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\product;

use app\common\exceptions\ApiException;
use app\common\log\AdminLog;
use app\model\product\ProductMemberPrice;
use app\service\BaseService;
use think\facade\Db;

/**
 * 会员价格服务类
 */
class ProductMemberPriceService extends BaseService
{
    protected ProductMemberPrice $productMemberPriceModel;

    public function __construct(ProductMemberPrice $productMemberPriceModel)
    {
        $this->productMemberPriceModel = $productMemberPriceModel;
    }

    /**
     * 获取筛选结果
     *
     * @param array $filter
     * @return array
     */
    public function getFilterResult(array $filter): array
    {
        $query = $this->filterQuery($filter);
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
    protected function filterQuery(array $filter): object
    {
        $query = $this->productMemberPriceModel->query();
        // 处理筛选条件

        if (!empty($filter['keyword'])) {
            $query->where('product_price_name', 'like', '%' . $filter['keyword'] . '%');
        }

        if ($filter['is_show'] > -1) {
            $query->where('is_show', $filter['is_show']);
        }

        if (!empty($filter['sort_field']) && !empty($filter['sort_order'])) {
            $query->order($filter['sort_field'], $filter['sort_order']);
        }
        return $query;
    }

    /**
     * 获取详情
     *
     * @param int $id
     * @return array
     * @throws ApiException
     */
    public function getDetail(int $id): array
    {
        $result = $this->productMemberPriceModel->where('price_id', $id)->find();

        if (!$result) {
            throw new ApiException('会员价格不存在');
        }

        return $result->toArray();
    }

    /**
     * 获取名称
     *
     * @param int $id
     * @return string|null
     */
    public function getName(int $id): ?string
    {
        return $this->productMemberPriceModel::where('price_id', $id)->value('product_price_name');
    }

    /**
     * 执行会员价格添加或更新
     *
     * @param int $id
     * @param array $data
     * @param bool $isAdd
     * @return int|bool
     * @throws ApiException
     */
    public function updateProductMemberPrice(int $id, array $data, bool $isAdd = false)
    {
        if ($isAdd) {
            $result = $this->productMemberPriceModel->save($data);
            AdminLog::add('新增会员价格:' . $data['product_price_name']);
            return $this->productMemberPriceModel->getKey();
        } else {
            if (!$id) {
                throw new ApiException('#id错误');
            }
            $result = $this->productMemberPriceModel->where('price_id', $id)->save($data);
            AdminLog::add('更新会员价格:' . $this->getName($id));

            return $result !== false;
        }
    }

    /**
     * 删除会员价格
     *
     * @param int $id
     * @return bool
     */
    public function deleteProductMemberPrice(int $id): bool
    {
        if (!$id) {
            throw new ApiException('#id错误');
        }
        $result = $this->productMemberPriceModel::destroy($id);

        if ($result) {
            AdminLog::add('删除会员价格:' . $this->getName($id));
        }

        return $result !== false;
    }

    //取得某商品的会员价格列表
    public function getMemberPriceList($product_id): array
    {
        /* 取得会员价格 */
        $price_list = array();
        $res = $this->productMemberPriceModel->field('user_rank, user_price')->where('product_id', $product_id)->cursor();
        foreach ($res as $row) {
            $price_list[$row['user_rank']] = $row['user_price'];
        }
        return $price_list;
    }

    /**
     * 删除会员价格
     * @param int $product_id
     * @param array $rank_list
     * @return void
     * @throws \think\db\exception\DbException
     */
    public function dealMemberPrice(int $product_id, array $rank_list): void
    {
        /* 循环处理每个会员等级 */
        foreach ($rank_list as $key => $rank) {
            /* 会员等级对应的价格 */
            $price = $rank['price'];
            // 插入或更新记录
            $count = ProductMemberPrice::where('product_id', $product_id)->where('user_rank', $rank['rank_id'])->count();
            if ($count > 0) {
                /* 如果会员价格是小于0则删除原来价格，不是则更新为新的价格 */
                if (!$price || $price <= 0) {
                    ProductMemberPrice::where('product_id', $product_id)->where('user_rank', $rank['rank_id'])->delete();
                } else {
                    ProductMemberPrice::where('product_id', $product_id)->where('user_rank', $rank['rank_id'])->save([
                        'user_price' => $price,
                    ]);
                }
            } else {
                if ($price > 0) {
                    ProductMemberPrice::create([
                        'product_id' => $product_id,
                        'user_rank' => $rank['rank_id'],
                        'user_price' => $price,
                    ]);
                }
            }
        }
    }
}
