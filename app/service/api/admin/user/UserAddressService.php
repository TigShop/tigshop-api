<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 收货地址
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\user;

use app\model\user\UserAddress;
use app\service\api\admin\BaseService;
use app\service\api\admin\setting\RegionService;
use exceptions\ApiException;

/**
 * 收货地址服务类
 */
class UserAddressService extends BaseService
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
    public function getAddressList(int $user_id): array
    {
        $result = UserAddress::where('user_id', $user_id)->limit(10)->append(['region_name'])->order('is_selected', 'desc')->select();
        return $result->toArray();
    }
    public function getUserSelectedAddress(int $user_id): array
    {
        $result = UserAddress::where('user_id', $user_id)->where('is_selected', 1)->find();
        return !empty($result) ? $result->toArray() : [];
    }

    /**
     * 设置地址选中
     * @param int $user_id
     * @param int $address_id
     * @return void
     * @throws ApiException
     */
    public function setAddressSelected(int $user_id, int $address_id): void
    {
        $address = UserAddress::where('user_id', $user_id)->find($address_id);
        if ($address) {
            $address->is_selected = 1;
            $address->save();
            UserAddress::where('user_id', $user_id)->where('address_id', '<>', $address_id)->update(['is_selected' => 0]);
        } else {
            throw new ApiException(/** LANG */'收货地址不存在');
        }
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
        $query = UserAddress::query();
        // 处理筛选条件

        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->where('consignee', 'like', '%' . $filter['keyword'] . '%');
        }

        if (isset($filter['is_show']) && $filter['is_show'] > -1) {
            $query->where('is_show', $filter['is_show']);
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
     * @return UserAddress
     * @throws ApiException
     */
    public function getAddressDetail(int $id, int $user_id): UserAddress
    {
        $result = UserAddress::where('address_id', $id)->where('user_id', $user_id)->find();

        if (!$result) {
            throw new ApiException(/** LANG */'收货地址不存在');
        }
        $result->region_ids = $result->region_ids ? $result->region_ids : [];
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
        return UserAddress::where('address_id', $id)->value('consignee');
    }

    /**
     *  添加收货地址
     * @param array $data
     * @param int $user_id
     * @return int
     */
    public function createUserAddress(array $data, int $user_id ): int
    {
        if (isset($data['region_ids'])) {
            $data['region_names'] = app(RegionService::class)->getNames($data['region_ids']);
        }
        $data['user_id'] = $user_id;
        $result = UserAddress::create($data);
        return $result->address_id;
    }

    /**
     * 执行收货地址更新
     *
     * @param int $id
     * @param array $data
     * @return int
     * @throws ApiException
     */
    public function updateUserAddress(int $id, int $user_id, array $data): int
    {
        if (isset($data['region_ids'])) {
            $data['region_names'] = app(RegionService::class)->getNames($data['region_ids']);
        }

        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = UserAddress::where('address_id', $id)->where('user_id', $user_id)->save($data);
        return $id;
    }

    /**
     * 删除收货地址
     *
     * @param int $id
     * @return bool
     */
    public function deleteUserAddress(int $id): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = UserAddress::destroy($id);

        return $result !== false;
    }

    /**
     * 收货地址列表
     * @param array $filter
     * @return array
     */
    public function userAddressList(array $filter): array
    {
        $query = UserAddress::where('user_id', request()->userId)
            ->append(["region_name"])
            ->field("address_id,user_id,consignee,email,region_names,address,telephone,mobile,is_selected,is_default");

        $count = $query->count();
        $list = $query->page($filter["page"], $filter["size"])->select();
        return [
            'count' => $count,
            'list' => $list,
        ];
    }

    /**
     * 设为选中
     * @param int $user_id
     * @param int $address_id
     * @return bool
     */
    public function addressSetSelected(int $user_id, int $address_id): bool
    {
        $address = UserAddress::where(['user_id' => $user_id, "is_selected" => 1])->find();
        if ($address) {
            $address->is_selected = 0;
            $address->save();
        }
        $is_selected = UserAddress::where(['user_id' => $user_id, "address_id" => $address_id])->save(["is_selected" => 1]);
        return $is_selected !== false;
    }
}
