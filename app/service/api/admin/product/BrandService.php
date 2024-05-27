<?php

namespace app\service\api\admin\product;

use app\model\product\Brand;
use app\service\api\admin\BaseService;
use app\validate\product\BrandValidate;
use exceptions\ApiException;
use log\AdminLog;
use think\facade\Db;
use utils\Util;

/**
 * 品牌服务类
 */
class BrandService extends BaseService
{
    protected Brand $brandModel;
    protected BrandValidate $brandValidate;

    public function __construct(Brand $brandModel)
    {
        $this->brandModel = $brandModel;
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
        $query = $this->brandModel->query();
        // 处理筛选条件

        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->where('brand_name', 'like', '%' . $filter['keyword'] . '%');
        }

        if (isset($filter['first_word']) && !empty($filter['first_word'])) {
            $query->where('first_word', 'like', '%' . $filter['first_word'] . '%');
        }

        if (isset($filter['is_show']) && $filter['is_show'] > -1) {
            $query->where('is_show', $filter['is_show']);
        }

        if (isset($filter['brand_is_hot']) && $filter['brand_is_hot'] > -1) {
            $query->where('brand_is_hot', $filter['brand_is_hot']);
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
     * @return Brand
     * @throws ApiException
     */
    public function getDetail(int $id): Brand
    {
        $result = Brand::where('brand_id', $id)->find();

        if (!$result) {
            throw new ApiException('品牌不存在');
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
        return $this->brandModel::where('brand_id', $id)->value('brand_name');
    }

    /**
     * 获取品牌集合
     * @param array $ids
     * @return array|null
     */
    public function getNames(array $ids): ?array
    {
        $result = $this->brandModel::whereIn('brand_id', $ids)->group('brand_id')->column('brand_name');
        return $result ? $result : [];
    }

    /**
     * 新增
     * @param array $data
     * @return \think\Model|Brand
     */
    public function add(array $data): \think\Model  | Brand
    {
        //增加默认值或修改某些值
        if (empty($data['first_word'])) {
            $data['first_word'] = Util::getFirstPinyin($data['brand_name']);
        }
        return Brand::create($data);
    }
    /**
     * 执行品牌更新
     *
     * @param int $id
     * @param array $data
     * @return Brand
     * @throws ApiException
     */
    public function edit(int $id, array $data): Brand
    {
        $item = $this->getDetail($id);
        $item->save($data);
        return $item;
    }

    /**
     * 更新单个字段
     *
     * @param int $id
     * @param array $data
     * @return int|bool
     * @throws ApiException
     */
    public function updateBrandField(int $id, array $data)
    {
        validate(BrandValidate::class)->only(array_keys($data))->check($data);
        if (!$id) {
            throw new ApiException('#id错误');
        }
        $result = $this->brandModel::where('brand_id', $id)->save($data);
        AdminLog::add('更新品牌:' . $this->getName($id));
        return $result !== false;
    }

    /**
     * 删除品牌
     *
     * @param int $id
     * @return bool
     */
    public function deleteBrand(int $id): bool
    {
        $get_name = $this->getName($id);
        $result = $this->brandModel::destroy($id);

        if ($result) {
            Db::name('product')->where('brand_id', $id)->update(['brand_id' => 0]);
            AdminLog::add('删除品牌:' . $get_name);
        }

        return $result !== false;
    }

    /**
     * 批量更新品牌首字母
     *
     * @return bool
     */
    public function batchUpdateFisrtWord(): bool
    {
        $brands = $this->brandModel::field('brand_id,brand_name')->where('first_word', '')->cursor();
        foreach ($brands as $brand) {
            $arr = ['first_word' => Util::getFirstPinyin($brand->brand_name) ?? '#'];
            $brand->save($arr);
        }
        AdminLog::add('品牌名称批量更新');
        return true;
    }

    /**
     * 获取品牌首字母列表和品牌列表
     *
     * @param string $word
     * @return array
     */
    public function getBrandWordList(string $word): array
    {
        $where = [['b.is_show', '=', 1]];
//        $brand_list = [];
//        $firstword_list = [];
//
//        if (request()->storeId) {
//            $where[] = ['a.store_id', '=', request()->storeId];
//            $where[] = ['a.check_status', '=', 1];
//        }

        if (!empty($word)) {
            $where[] = ['b.first_word', '=', $word];
        }

        $query = Db::name('brand')->alias('b')->where($where);

        $brand_list = $query->field('b.brand_id,b.brand_name,b.first_word')->select();
        $firstword_list = $query->order('b.first_word')->distinct(true)->column('b.first_word');

        return [
            'brand_list' => $brand_list,
            'firstword_list' => $firstword_list,
        ];
    }
}
