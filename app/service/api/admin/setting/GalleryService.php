<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 相册
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\setting;

use app\common\exceptions\ApiException;
use app\common\log\AdminLog;
use app\model\setting\Gallery;
use app\model\setting\GalleryPic;
use app\service\api\admin\BaseService;

/**
 * 相册服务类
 */
class GalleryService extends BaseService
{
    protected Gallery $galleryModel;
    protected GalleryPic $galleryPicModel;

    public function __construct(Gallery $galleryModel, GalleryPic $galleryPicModel)
    {
        $this->galleryModel = $galleryModel;
        $this->galleryPicModel = $galleryPicModel;
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
        $result = $query->page($filter['page'], $filter['size'])->select()->toArray();
        foreach ($result as $key => $value) {
            $result[$key]['gallery_pics'] = app(GalleryPicService::class)->getFilterResult([
                'page' => 1,
                'size' => 4,
                'gallery_id' => $value['gallery_id'],
                'sort_field' => 'pic_id',
                'sort_order' => 'desc',
            ]
            );
        }
        return $result;
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
        $query = $this->galleryModel->query();
        // 处理筛选条件
        $query->where('store_id', request()->storeId);

        $query->where('parent_id', $filter['gallery_id'] > 0 ? $filter['gallery_id'] : 0);

        if (isset($filter['sort_field'], $filter['sort_order']) && !empty($filter['sort_field']) && !empty($filter['sort_order'])) {
            $query->order($filter['sort_field'], $filter['sort_order']);
        }
        return $query;
    }

    /**
     * 获取详情
     * @param int $id
     * @return Gallery
     * @throws ApiException
     */
    public function getDetail(int $id): Gallery
    {
        $result = $this->galleryModel->where('gallery_id', $id)->find();
        if (!$result) {
            throw new ApiException(/** LANG */'相册不存在');
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
        return $this->galleryModel::where('gallery_id', $id)->value('gallery_name');
    }

    /**
     * 创建相册
     * @param array $data
     * @return int
     */
    public function createGallery(array $data): int
    {
        $result = $this->galleryModel->save($data);
        AdminLog::add('新增相册:' . $data['gallery_name']);
        return $this->galleryModel->getKey();
    }

    /**
     * 执行相册更新
     * @param int $id
     * @param array $data
     * @return bool
     * @throws ApiException
     */
    public function updateGallery(int $id, array $data): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = $this->galleryModel->where('gallery_id', $id)->save($data);
        AdminLog::add('更新相册:' . $this->getName($id));
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
    public function updateGalleryField(int $id, array $data)
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = $this->galleryModel::where('gallery_id', $id)->save($data);
        AdminLog::add('更新相册:' . $this->getName($id));
        return $result !== false;
    }

    /**
     * 删除相册
     *
     * @param int $id
     * @return bool
     */
    public function deleteGallery(int $id): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $get_name = $this->getName($id);
        $result = $this->galleryModel::destroy($id);

        if ($result) {
            AdminLog::add('删除相册:' . $get_name);
        }

        return $result !== false;
    }
}
