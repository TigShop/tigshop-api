<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 示例模板
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\example;

use app\common\exceptions\ApiException;
use app\common\log\AdminLog;
use app\model\example\Example;
use app\service\api\admin\BaseService;
use app\validate\example\ExampleValidate;
use think\Collection;

/**
 * 示例模板服务类
 */
class ExampleService extends BaseService
{
    protected ExampleValidate $exampleValidate;

    public function __construct()
    {
    }

    /**
     * 获取筛选结果
     *
     * @param array $filter
     * @return array
     */
    public function getFilterResult(array $filter): Collection
    {
        $query = $this->filterQuery($filter);
        if (!empty($filter['sort_field']) && !empty($filter['sort_order'])) {
            $query->order($filter['sort_field'], $filter['sort_order']);
        }
        return $query->page($filter['page'], $filter['size'])->select();
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
        $query = Example::query();
        // 处理筛选条件

        if (!empty($filter['keyword'])) {
            $query->where('example_name', 'like', '%' . $filter['keyword'] . '%');
        }

        if (isset($filter['is_show']) && $filter['is_show'] > -1) {
            $query->where('is_show', $filter['is_show']);
        }

        return $query;
    }

    /**
     * 获取详情
     *
     * @param int $id
     * @return Example
     * @throws ApiException
     */
    public function getDetail(int $id): Example
    {
        $result = Example::where('example_id', $id)->find();

        if (!$result) {
            throw new ApiException('示例模板不存在');
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
        return Example::where('example_id', $id)->value('example_name');
    }

    /**
     * 新增方法
     * @param array $data
     * @return \think\Model|Example
     */
    public function create(array $data): \think\Model|Example
    {
        //增加默认值或修改某些值
        $data['is_show'] = 1;
        $data['status'] = 1;
        return Example::create($data);
    }

    /**
     * 删除
     * @param int $id
     * @param int $user_id
     * @return bool
     * @throws ApiException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function del(int $id, int $userId): bool
    {
        $item = $this->getDetail($id);
        //model数据判断层面的校验放对应模型的model中
        if (!$item->canDelete($userId)) {
            throw new ApiException('#无删除权限');
        }
        $result = $item->delete();
        if ($result) {
            AdminLog::add('删除示例模板:' . $this->getName($id));
        }
        return $result;
    }

    /**
     * @param array $data
     * @param int $id
     * @param int $userId
     * @throws ApiException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function update(array $data, int $id, int $userId): Example
    {
        $item = $this->getDetail($id);
        //model数据判断层面的校验放对应模型的model中
        if (!$item->canEdit($userId)) {
            throw new ApiException('#无删除权限');
        }
        $result = $item->save($data);
        if (!$result) {
            throw new ApiException('更新失败');
        }
        return $item;
    }
}
