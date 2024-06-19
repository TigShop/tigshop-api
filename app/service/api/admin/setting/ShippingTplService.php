<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 运费模板
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\setting;

use app\model\setting\ShippingTpl;
use app\model\setting\ShippingTplInfo;
use app\service\core\BaseService;
use exceptions\ApiException;
use log\AdminLog;

/**
 * 运费模板服务类
 */
class ShippingTplService extends BaseService
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
    public function filterQuery(array $filter): object
    {
        $query = ShippingTpl::query();
        // 处理筛选条件
        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->where('shipping_tpl_name', 'like', '%' . $filter['keyword'] . '%');
        }

        if (isset($filter['sort_field'], $filter['sort_order']) && !empty($filter['sort_field']) && !empty($filter['sort_order'])) {
            $query->order($filter['sort_field'], $filter['sort_order']);
        }

        if (isset($filter['shop_id'])) {
            $query->where('shop_id', '=',$filter['shop_id']);
        }
        return $query;
    }

    /**
     * 获取详情
     *
     * @param int $id
     * @return ShippingTpl
     * @throws ApiException
     */
    public function getDetail(int $id): ShippingTpl
    {
        $result = ShippingTpl::where('shipping_tpl_id', $id)->find();

        if (!$result) {
            throw new ApiException(/** LANG */'运费模板不存在');
        }

        return $result;
    }

    /**
     * 获取名称
     *
     * @param int $id
     * @return string|null
     */
    public function getName(int $id): ?string
    {
        return ShippingTpl::where('shipping_tpl_id', $id)->value('shipping_tpl_name');
    }

    /**
     * 获取通用运费模板信息
     * @param array $data
     * @return array
     */
    public function getCommonShippingData(array $data): array
    {
        // 模板基本信息
        $shipping_tpl_arr = [
            "shipping_tpl_name" => $data["shipping_tpl_name"],
            "shipping_time" => $data["shipping_time"],
            "is_free" => $data["is_free"],
            "pricing_type" => $data["pricing_type"],
            "is_default" => $data["is_default"],
        ];
        return $shipping_tpl_arr;
    }

    /**
     * 添加运费模板
     * @param array $data
     * @return int
     */
    public function createShippingTpl(array $data): int
    {
        $shipping_tpl_info = $data["shipping_tpl_info"];
        unset($data["shipping_tpl_info"]);
        //处理运费模板信息
        if ($data["is_default"] != 0) {
            ShippingTpl::where("shop_id", request()->shopId)->update(["is_default" => 0]);
        }
        $shipping_tpl_arr = $this->getCommonShippingData($data);

        $shipping_tpl_arr["shop_id"] = $data["shop_id"];
        $shipping_tpl = ShippingTpl::create($shipping_tpl_arr);
        $id = $shipping_tpl->shipping_tpl_id;
        // 保存运费模板信息
        $this->saveShippingTplInfo($shipping_tpl_info, $id, $data);
        AdminLog::add('新增运费模板:' . $data['shipping_tpl_name']);
        return $id;
    }

    /**
     * 执行运费模板更新
     * @param int $id
     * @param array $data
     * @return bool
     * @throws ApiException
     */
    public function updateShippingTpl(int $id, array $data): bool
    {
        $shipping_tpl_info = $data["shipping_tpl_info"];
        unset($data["shipping_tpl_info"]);

        //处理运费模板信息
        if ($data["is_default"] != 0) {
            ShippingTpl::where("shipping_tpl_id", $id)->update(["is_default" => 0]);
        }

        $shipping_tpl_arr = $this->getCommonShippingData($data);

        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = ShippingTpl::where('shipping_tpl_id', $id)->save($shipping_tpl_arr);

        $this->saveShippingTplInfo($shipping_tpl_info, $id, $data);

        AdminLog::add('更新运费模板:' . $this->getName($id));

        return $result !== false;
    }
    /**
     * 更新单个字段
     *
     * @param int $id
     * @param array $data
     * @return bool
     * @throws ApiException
     */
    public function updateShippingTplField(int $id, array $data): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = ShippingTpl::where('example_id', $id)->save($data);
        AdminLog::add('更新示例模板:' . $this->getName($id));
        return $result !== false;
    }

    /**
     * 保存运费模板信息
     * @param array $shipping_tpl_info
     * @param int $id
     * @param array $data
     * @return void
     */
    public function saveShippingTplInfo(array $shipping_tpl_info, int $id, array $data): void
    {
        $shippingTplInfo = new ShippingTplInfo();
        $shippingTplInfo->where('shipping_tpl_id', $id)->delete();
        $data = [];
        if ($shipping_tpl_info) {
            foreach ($shipping_tpl_info as $key => $value) {
                if ($value['is_checked']) {
                    $data = array_merge([$value['default_tpl_info']], $value['area_tpl_info']);
                    foreach ($data as $k => $row) {
                        unset($data[$k]['id']);
                        $data[$k]['shipping_tpl_id'] = $id;
                    }
                    $shippingTplInfo->saveAll($data);
                }
            }
        }
    }
    /**
     * 删除运费模板
     *
     * @param int $id
     * @return bool
     */
    public function deleteShippingTpl(int $id): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $get_name = $this->getName($id);
        $result = ShippingTpl::destroy($id);

        if ($result) {
            AdminLog::add('删除运费模板:' . $get_name);
        }

        return $result !== false;
    }

    /**
     * 获取运送方式
     * @param bool $isAdd
     * @return array
     */
    public function getShippingTplInfo(int $id = 0): array
    {
        $shipping_type = app(ShippingTypeService::class)->getAllShippingType();
        if ($id) {
            foreach ($shipping_type as $key => $value) {
                $tpl_info = ShippingTplInfo::where(
                    [
                        ["shipping_tpl_id", '=', $id],
                        ["shipping_type_id", '=', $value['shipping_type_id']],
                    ]
                )->select();
                $tpl_info = $tpl_info ? $tpl_info->toArray() : [];
                $shipping_type[$key]['default_tpl_info'] = null;
                $shipping_type[$key]['area_tpl_info'] = [];
                foreach ($tpl_info as $row) {
                    if ($row['is_default'] == 1) {
                        $shipping_type[$key]['default_tpl_info'] = $row;
                    } else {
                        $shipping_type[$key]['area_tpl_info'][] = $row;
                    }
                }
            }
        }
        return $shipping_type;
    }

    /**
     * 获取运费模板信息
     * @param array $data
     * @return array
     */
    public function getShippingDataInfo(array $data): array
    {
        $result = [];
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $result[$key]["shipping_tpl_id"] = 0;
                $result[$key]["shipping_type_id"] = $value["shipping_type_id"];
                $result[$key]["is_default"] = 1;
                $result[$key]["start_number"] = "1.0";
                $result[$key]["start_price"] = "0.00";
                $result[$key]["add_number"] = "1.0";
                $result[$key]["add_price"] = "0.00";
                $result[$key]["free_price"] = "0.00";
                $result[$key]["region_data"] = [
                    "area_regions" => [],
                    "area_region_names" => [],
                ];
                $result[$key]["shipping_type_name"] = $value["shipping_type_name"];
            }
        }
        return $result;
    }

    /**
     * 获取店铺下商品的所有运费模板
     * @param $shop_id
     * @return int
     */
    public function getDefaultShippingTplId($shop_id = 0): int
    {
        return ShippingTpl::where('shop_id', $shop_id)->where('is_default', 1)->value('shipping_tpl_id') || 0;
    }
}
