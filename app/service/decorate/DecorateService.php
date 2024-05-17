<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 装修
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\decorate;

use app\common\exceptions\ApiException;
use app\common\utils\Time;
use app\model\decorate\Decorate;
use app\service\BaseService;

/**
 * 装修服务类
 */
class DecorateService extends BaseService
{
    protected Decorate $decorateModel;

    public function __construct(Decorate $decorateModel)
    {
        $this->decorateModel = $decorateModel;
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
        $query = $this->decorateModel->query();
        // 处理筛选条件

        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->where('decorate_title', 'like', '%' . $filter['keyword'] . '%');
        }

        // 页面类型检索
        if (isset($filter["decorate_type"]) && !empty($filter["decorate_type"])) {
            $query->where('decorate_type', $filter['decorate_type']);
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
     * @return Decorate
     * @throws ApiException
     */
    public function getDetail(int $id): Decorate
    {
        $result = $this->decorateModel->where('decorate_id', $id)->find();

        if (!$result) {
            throw new ApiException(/** LANG */'装修不存在');
        }

        return $result;
    }

    /**
     * 加载草稿数据
     * @param int $id
     * @return array
     * @throws ApiException
     */
    public function loadDraftData(int $id): array
    {
        $detail = $this->getDetail($id);
        return $detail['draft_data'] ?? [];
    }
    /**
     * 获取名称
     *
     * @param int $id
     * @return string|null
     */
    public function getName(int $id): ?string
    {
        return $this->decorateModel::where('decorate_id', $id)->value('decorate_title');
    }

    /**
     * 添加装修
     * @param array $data
     * @return int
     */
    public function createDecorate(array $data):int
    {
        $result = $this->decorateModel->save($data);
        return $this->decorateModel->getKey();
    }

    /**
     * 执行装修更新
     *
     * @param int $id
     * @param array $data
     * @param bool $isAdd
     * @return int|bool
     * @throws ApiException
     */
    public function updateDecorate(int $id, array $data)
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = $this->decorateModel->where('decorate_id', $id)->save($data);
        return $result !== false;
    }
    /**
     * 发布并保存模板
     *
     * @param integer $id
     * @param array $data
     * @return bool
     */
    public function publishDecorate(int $id, array $data): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $data['status'] = 1;
        $data['draft_data'] = '';
        $data['update_time'] = Time::now();
        $result = $this->decorateModel->where('decorate_id', $id)->save($data);
        return $result !== false;
    }

    /**
     * 保存草稿
     * @param int $id
     * @param array $draft_data
     * @return bool
     * @throws ApiException
     */
    public function saveDecoratetoDraft(int $id, array $draft_data): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $data = [
            'draft_data' => $draft_data,
        ];
        $result = $this->decorateModel->where('decorate_id', $id)->save($data);
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
    public function updateDecorateField(int $id, array $data)
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = $this->decorateModel::where('decorate_id', $id)->save($data);
        return $result !== false;
    }

    /**
     * 删除装修
     *
     * @param int $id
     * @return bool
     */
    public function deleteDecorate(int $id): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = $this->decorateModel::destroy($id);
        return $result !== false;
    }

    /**
     * 获取移动端首页发布的页面
     * @return array
     * @throws ApiException
     */
    public function getAppHomeDecorate(): array
    {
        return $this->getDecorateModule(Decorate::TYPE_H5, true);
    }

    /**
     * 获取PC端首页发布的页面
     * @return array
     * @throws ApiException
     */
    public function getPcHomeDecorate(): array
    {
        return $this->getDecorateModule(Decorate::TYPE_PC, true);
    }

    /**
     * 获取PC端预览数据
     * @param int $decorate_id
     * @return array
     * @throws ApiException
     */
    public function getPcPreviewDecorate(int $decorate_id): array
    {
        return $this->getPreviewDecorate(Decorate::TYPE_PC, $decorate_id);
    }

    /**
     * 获取移动端预览数据
     * @param int $decorate_id
     * @return array
     * @throws ApiException
     */
    public function getAppPreviewDecorate(int $decorate_id): array
    {
        return $this->getPreviewDecorate(Decorate::TYPE_H5, $decorate_id);
    }

    /**
     * 获取预览数据
     * @param int $type
     * @param int $decorate_id
     * @return array
     * @throws ApiException
     */
    public function getPreviewDecorate(int $type, int $decorate_id): array
    {
        $result = $this->decorateModel->where('decorate_type', $type)->where('decorate_id', $decorate_id)->find();
        $result = $result ? $result->toArray() : $result;
        if (!$result) {
            throw new ApiException(/** LANG */'模板不存在' . $decorate_id);
        }
        foreach ($result['draft_data']['moduleList'] as $key => $item) {
            $result['draft_data']['moduleList'][$key]['module'] = $this->formatModule($item['type'], $item['module']);
        }
        return [
            'decorate_id' => $result['decorate_id'],
            'module_list' => $result['draft_data']['moduleList'],
        ];
    }

    /**
     * 获取页面模块信息
     * @param string $type
     * @param boolean $is_home 是否首页
     * @param integer $status 是否已发布
     * @return array
     */
    public function getDecorateModule(string $type, bool $is_home = false, int $status = 1, bool $is_draft = false): array
    {
        $result = $this->decorateModel->where('decorate_type', $type)
            ->where('is_home', $is_home)
            ->where('status', $status)->find();
        $result = $result ? $result->toArray() : $result;
        if (!$result) {
            throw new ApiException(/** LANG */'模板不存在');
        }
        foreach ($result['data']['moduleList'] as $key => $item) {
            $result['data']['moduleList'][$key]['module'] = $this->formatModule($item['type'], $item['module']);
        }
        return [
            'decorate_id' => $result['decorate_id'],
            'module_list' => $result['data']['moduleList'],
        ];
    }

    /**
     * 格式化模块
     * @param string $type
     * @param array $module
     * @param array|null $params
     * @return array
     */
    public function formatModule(string $type, array $module, array $params = null): array
    {
        $class = 'app\\service\\decorate\\modules\\' . str_replace('_', '', ucwords($type, '_') . 'Service');
        if (class_exists($class)) {
            $moduleClass = new $class();
            $module = $moduleClass->formatData($module, $params);
        }
        return $module;
    }

    /**
     * 获取指定模块的数据
     * @param $decorate_id
     * @param $module_index
     * @param array $params
     * @return array
     * @throws ApiException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getDecorateModuleData($decorate_id, $module_index, array $params = []): array
    {
        $result = $this->decorateModel->find($decorate_id);
        $result = $result ? $result->toArray() : $result;
        if (!$result) {
            throw new ApiException(/** LANG */'模板不存在');
        }
        $module = [];
        foreach ($result['data']['moduleList'] as $key => $item) {
            if (isset($item['module_index']) && $item['module_index'] == $module_index) {
                $module = $this->formatModule($item['type'], $item['module'], $params);
            }
        }
        return $module;
    }
}
