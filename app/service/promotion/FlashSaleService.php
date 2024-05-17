<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 秒杀
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\promotion;

use app\common\exceptions\ApiException;
use app\common\log\AdminLog;
use app\model\promotion\FlashSale;
use app\service\BaseService;
use app\validate\promotion\FlashSaleValidate;

/**
 * 秒杀服务类
 */
class FlashSaleService extends BaseService
{
    protected FlashSaleValidate $flashSaleValidate;

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
    public function filterQuery(array $filter): object
    {
        $query = FlashSale::query();
        // 处理筛选条件

        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->where('flash_sale_name', 'like', '%' . $filter['keyword'] . '%');
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
     * @return array
     * @throws ApiException
     */
    public function getDetail(int $id): array
    {
        $result = FlashSale::where('flash_sale_id', $id)->find();

        if (!$result) {
            throw new ApiException('秒杀不存在');
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
        return FlashSale::where('flash_sale_id', $id)->value('flash_sale_name');
    }

    /**
     * 执行秒杀添加或更新
     *
     * @param int $id
     * @param array $data
     * @param bool $isAdd
     * @return int|bool
     * @throws ApiException
     */
    public function updateFlashSale(int $id, array $data, bool $isAdd = false)
    {
        validate(FlashSaleValidate::class)->only(array_keys($data))->check($data);
        if ($isAdd) {
            $result = FlashSale::create($data);
            AdminLog::add('新增秒杀:' . $data['flash_sale_name']);
            return $result->getKey();
        } else {
            if (!$id) {
                throw new ApiException('#id错误');
            }
            $result = FlashSale::where('flash_sale_id', $id)->save($data);
            AdminLog::add('更新秒杀:' . $this->getName($id));

            return $result !== false;
        }
    }

    /**
     * 更新单个字段
     *
     * @param int $id
     * @param array $data
     * @return int|bool
     * @throws ApiException
     */
    public function updateFlashSaleField(int $id, array $data)
    {
        validate(FlashSaleValidate::class)->only(array_keys($data))->check($data);
        if (!$id) {
            throw new ApiException('#id错误');
        }
        $result = FlashSale::where('flash_sale_id', $id)->save($data);
        AdminLog::add('更新秒杀:' . $this->getName($id));
        return $result !== false;
    }

    /**
     * 删除秒杀
     *
     * @param int $id
     * @return bool
     */
    public function deleteFlashSale(int $id): bool
    {
        if (!$id) {
            throw new ApiException('#id错误');
        }
        $get_name = $this->getName($id);
        $result = FlashSale::destroy($id);

        if ($result) {
            AdminLog::add('删除秒杀:' . $get_name);
        }

        return $result !== false;
    }
}
