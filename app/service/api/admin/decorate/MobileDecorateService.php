<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 首页装修模板
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\decorate;

use app\model\decorate\Decorate as MobileDecorate;
use app\service\core\BaseService;
use exceptions\ApiException;
use utils\Time;

/**
 * 首页装修模板服务类
 */
class MobileDecorateService extends BaseService
{
    protected MobileDecorate $mobileDecorateModel;

    public function __construct(MobileDecorate $mobileDecorateModel)
    {
        $this->mobileDecorateModel = $mobileDecorateModel;
    }

    /**
     * 获取筛选结果
     *
     * @param array $filter
     * @return array
     */
    public function getFilterResult(array $filter): array
    {
        $query = $this->filterQuery($filter)->field('decorate_title,decorate_id,update_time,status,is_home')->append(['type_name']);
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
        $query = $this->mobileDecorateModel->query();
        // 装修类型
        if (isset($filter['decorate_type']) && !empty($filter['decorate_type'])) {
            $query->where('decorate_type', $filter['decorate_type']);
        } else {
            $query->where('decorate_type', 1);
        }

        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->where('decorate_title', 'like', '%' . $filter['keyword'] . '%');
        }
        if (isset($filter['sort_field'], $filter['sort_order']) && !empty($filter['sort_field']) && !empty($filter['sort_order'])) {
            $query->order(['is_home' => 'desc', $filter['sort_field'] => $filter['sort_order']]);
        }
        return $query;
    }

    /**
     * 获取详情
     *
     * @param int $id
     * @return MobileDecorate
     * @throws ApiException
     */
    public function getDetail(int $id): MobileDecorate
    {
        $result = $this->mobileDecorateModel->where('decorate_id', $id)->find();
        if (!$result) {
            throw new ApiException(/** LANG */'首页装修模板不存在');
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
        return $this->mobileDecorateModel::where('decorate_id', $id)->value('decorate_title');
    }

    /**
     * 添加首页装修模板
     * @param array $data
     * @return int
     */
    public function createMobileDecorate(array $data): int
    {
        $result = $this->mobileDecorateModel->save($data);
        return $this->mobileDecorateModel->getKey();
    }

    /**
     * 执行首页装修模板更新
     *
     * @param int $id
     * @param array $data
     * @return bool
     * @throws ApiException
     */
    public function updateMobileDecorate(int $id, array $data): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = $this->mobileDecorateModel->where('decorate_id', $id)->save($data);
        return $result !== false;
    }

    /**
     * 设置首页
     * @param int $id
     * @return bool
     * @throws ApiException
     */
    public function setHome(int $id): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $item = $this->mobileDecorateModel::where('decorate_id', $id)->find();
        if (!$item) {
            throw new ApiException(/** LANG */'#不存在的模板');
        }
        MobileDecorate::where('is_home', 1)->where('decorate_type', $item->decorate_type)->save([
            'is_home' => 0,
        ]);
        $result = $item->save([
            'is_home' => 1,
        ]);
        return $result !== false;
    }

    /**
     * 复制
     * @param int $id
     * @return bool
     * @throws ApiException
     */
    public function copy(int $id): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $item = MobileDecorate::where('decorate_id', $id)->find();
        if (!$item) {
            throw new ApiException(/** LANG */'#未找到数据');
        }
        $new_data = $item->getData();
        unset($new_data['decorate_id']);
        $new_data['is_home'] = 0;
        $new_data['decorate_title'] = $new_data['decorate_title'] . '-复制';
        $new_data['update_time'] = Time::now();
        $mobileDecorate = new MobileDecorate();
        $result = $mobileDecorate->save($new_data);
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
    public function updateMobileDecorateField(int $id, array $data)
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = $this->mobileDecorateModel::where('decorate_id', $id)->save($data);
        return $result !== false;
    }

    /**
     * 删除首页装修模板
     *
     * @param int $id
     * @return bool
     */
    public function deleteMobileDecorate(int $id): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = $this->mobileDecorateModel::destroy($id);
        return $result !== false;
    }
    public function getPcHomeData(): array
    {
        $result = $this->mobileDecorateModel->where('decorate_type', MobileDecorate::TYPE_PC)->where('status', 1)->findOrEmpty()->toArray();
        if (empty($result)) {
            throw new ApiException(/** LANG */'首页装修模板不存在');
        }
        return $result['data']['moduleList'];
    }

}
