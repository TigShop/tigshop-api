<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 相册图片
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\setting;

use app\model\setting\GalleryPic;
use app\service\core\BaseService;
use exceptions\ApiException;
use log\AdminLog;

/**
 * 相册图片服务类
 */
class GalleryPicService extends BaseService
{
    protected GalleryPic $galleryPicModel;

    public function __construct(GalleryPic $galleryPicModel)
    {
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
        $query = $this->galleryPicModel->query();
        // 处理筛选条件
        $query->where('shop_id', request()->shopId);

        if (isset($filter['gallery_id']) && $filter['gallery_id'] !== null && $filter['gallery_id'] > 0) {
            $query->where('gallery_id', $filter['gallery_id']);
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
     * @return GalleryPic
     * @throws ApiException
     */
    public function getDetail(int $id): GalleryPic
    {
        $result = $this->galleryPicModel->where('pic_id', $id)->find();

        if (!$result) {
            throw new ApiException(/** LANG */'相册图片不存在');
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
        return $this->galleryPicModel::where('pic_id', $id)->value('pic_name');
    }

    /**
     * 创建相册图片
     * @param array $data
     * @return int
     */
    public function createGalleryPic(array $data): int
    {
        $result = $this->galleryPicModel->save($data);
        AdminLog::add('新增相册图片:' . $data['pic_name']);
        return $this->galleryPicModel->getKey();
    }

    /**
     * 执行相册图片更新
     *
     * @param int $id
     * @param array $data
     * @return bool
     * @throws ApiException
     */
    public function updateGalleryPic(int $id, array $data): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = $this->galleryPicModel->where('pic_id', $id)->save($data);
        AdminLog::add('更新相册图片:' . $this->getName($id));
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
    public function updateGalleryPicField(int $id, array $data): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $result = $this->galleryPicModel::where('pic_id', $id)->save($data);
        AdminLog::add('更新相册图片:' . $this->getName($id));
        return $result !== false;
    }

    /**
     * 删除相册图片
     *
     * @param int $id
     * @return bool
     */
    public function deleteGalleryPic(int $id): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $get_name = $this->getName($id);
        $result = $this->galleryPicModel::destroy($id);

        if ($result) {
            AdminLog::add('删除相册图片:' . $get_name);
        }

        return $result !== false;
    }
}
