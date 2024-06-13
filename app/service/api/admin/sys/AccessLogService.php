<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 访问日志
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\sys;

use app\model\sys\AccessLog;
use app\service\core\BaseService;
use exceptions\ApiException;
use log\AdminLog;
use utils\Time;

/**
 * 访问日志服务类
 */
class AccessLogService extends BaseService
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
        $query = AccessLog::query();
        // 处理筛选条件
        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->where('access_path', 'like', '%' . $filter['keyword'] . '%');
        }

        // 店铺检索
        if (isset($filter['shop_id']) && $filter['shop_id'] > -1) {
            $query->where('shop_id', $filter['shop_id']);
        }

        // 访问时间
        if (isset($filter['access_time']) && !empty($filter['access_time'])) {
            $filter['access_time'] = is_array($filter['access_time']) ? $filter['access_time'] : explode(',', $filter['access_time']);
            list($start_date, $end_date) = $filter['access_time'];
            $start_date = Time::toTime($start_date);
            $end_date = Time::toTime($end_date) + 86400;
            $query->whereTime('access_time', "between", [$start_date, $end_date]);
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
        $result = AccessLog::where('id', $id)->find();

        if (!$result) {
            throw new ApiException('访问日志不存在');
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
        return AccessLog::where('id', $id)->value('access_path');
    }

    /**
     * 执行访问日志添加或更新
     *
     * @param int $id
     * @param array $data
     * @param bool $isAdd
     * @return int|bool
     * @throws ApiException
     */
    public function updateAccessLog(int $id, array $data, bool $isAdd = false)
    {
        if ($isAdd) {
            $result = AccessLog::create($data);
            AdminLog::add('新增访问日志:' . $data['access_path']);
            return $result->getKey();
        } else {
            if (!$id) {
                throw new ApiException('#id错误');
            }
            $result = AccessLog::where('id', $id)->save($data);
            AdminLog::add('更新访问日志:' . $this->getName($id));

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
    public function updateAccessLogField(int $id, array $data)
    {
        if (!$id) {
            throw new ApiException('#id错误');
        }
        $result = AccessLog::where('id', $id)->save($data);
        AdminLog::add('更新访问日志:' . $this->getName($id));
        return $result !== false;
    }

    /**
     * 删除访问日志
     *
     * @param int $id
     * @return bool
     */
    public function deleteAccessLog(int $id): bool
    {
        if (!$id) {
            throw new ApiException('#id错误');
        }
        $get_name = $this->getName($id);
        $result = AccessLog::destroy($id);

        if ($result) {
            AdminLog::add('删除访问日志:' . $get_name);
        }

        return $result !== false;
    }

    /**
     * 获取访客量 / 浏览量
     *
     * @param array $data
     * @return int
     */
    public function getVisitNum(array $data, int $access_flag = 0, int $product_flag = 0, int $shopId = 0)
    {
        $query = $this->filterQuery([
            'access_time' => $data,
            'shop_id' => $shopId,
        ]);
        if ($access_flag) {
            // 浏览量
            if ($product_flag) {
                $query->where("product_id", ">", 0);
            }
            return $query->count();
        } else {
            // 访客量
            if ($product_flag) {
                $query->where("product_id", ">", 0);
            }
            return $query->group('ip_address')->count();
        }
    }

    /**
     * 获取商品访问量 / 浏览量
     *
     * @param array $data
     * @return int
     */
    public function getVisitList(array $data, int $access_flag = 0, int $product_flag = 0, int $shopId = 0)
    {
        if ($access_flag) {
            // 浏览量
            $query = $this->filterQuery([
                'access_time' => $data,
                'shop_id' => $shopId,
            ])->field("DATE_FORMAT(FROM_UNIXTIME(access_time), '%Y-%m-%d') AS period")
              ->field("COUNT(*) AS access_count")
              ->group("period");
        } else {
            // 访客量
            $query = $this->filterQuery([
                'access_time' => $data,
                'shop_id' => $shopId,
            ])->field("DATE_FORMAT(FROM_UNIXTIME(access_time), '%Y-%m-%d') AS period")
                ->field("COUNT(DISTINCT ip_address) as access_count")
                ->group("period");
        }
        if ($product_flag) {
            $query = $query->where("product_id", ">", 0);
        }
        return $query->select()->toArray();
    }

    /**
     * 获取访问量 / 浏览量  带去重
     *
     * @param array $data
     * @return int
     */
    public function getDistinctCount(array $data, int $access_flag = 0, int $goods_flag = 0)
    {
        if ($access_flag) {
            // 浏览量
            $query = AccessLog::field("COUNT(DISTINCT ip_address) as visit_num")->accessTime($data)->storePlatform();
            if ($goods_flag == 1) {
                $query->where("product_id", ">", 0);
            }
            return $query->select();
        } else {
            // 访客量
            return AccessLog::field("COUNT(DISTINCT ip_address) as visit_num")->accessTime($data)->storePlatform()->group('ip_address')->count();
        }
    }

}
