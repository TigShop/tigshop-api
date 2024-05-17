<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 相册图片
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\admin\controller\setting;

use app\admin\AdminBaseController;
use app\common\exceptions\ApiException;
use app\common\utils\Time;
use app\service\image\Image;
use app\service\setting\GalleryPicService;
use app\service\setting\GalleryService;
use app\validate\setting\GalleryPicValidate;
use think\App;
use think\exception\ValidateException;
use think\facade\Db;
use think\Response;

/**
 * 相册图片控制器
 */
class GalleryPic extends AdminBaseController
{
    protected GalleryPicService $galleryPicService;
    protected GalleryService $galleryService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param GalleryPicService $galleryPicService
     */
    public function __construct(App $app, GalleryPicService $galleryPicService, GalleryService $galleryService)
    {
        parent::__construct($app);
        $this->galleryPicService = $galleryPicService;
        $this->galleryService = $galleryService;
        $this->checkAuthor('galleryPicManage'); //权限检查
    }

    /**
     * 列表页面
     *
     * @return Response
     */
    public function list(): Response
    {
        $filter = $this->request->only([
            'page/d' => 1,
            'size/d' => 15,
            'gallery_id/d' => 0,
            'sort_field' => 'pic_id',
            'sort_order' => 'desc',
        ], 'get');

        $filterResult = $this->galleryPicService->getFilterResult($filter);
        $total = $this->galleryPicService->getFilterCount($filter);
        if ($filter['gallery_id'] > 0) {
            $child_gallery_list = $this->galleryService->getFilterResult([
                'gallery_id' => $filter['gallery_id'],
                'page' => 1,
                'size' => 99,
                'sort_field' => 'gallery_id',
                'sort_order' => 'asc',
            ]);
            $gallery_info = $this->galleryService->getDetail($filter['gallery_id']);
        }
        return $this->success([
            'filter_result' => $filterResult,
            'child_gallery_list' => $child_gallery_list ?? [],
            'gallery_info' => $gallery_info ?? [],
            'filter' => $filter,
            'total' => $total,
        ]);
    }

    /**
     * 详情
     * @return Response
     */
    public function detail(): Response
    {
        $id = input('id/d', 0);
        $item = $this->galleryPicService->getDetail($id);
        return $this->success([
            'item' => $item,
        ]);
    }

    /**
     * 添加
     * @return Response
     */
    public function create(): Response
    {
        $data = $this->request->only([
            'pic_name' => '',
            'galleryPic_desc' => '',
            'galleryPic_pic' => '',
            'is_show/d' => 1,
            'sort_order/d' => 50,
        ], 'post');

        try {
            validate(GalleryPicValidate::class)
                ->scene('create')
                ->check($data);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        $result = $this->galleryPicService->createGalleryPic($data);
        if ($result) {
            return $this->success(/** LANG */'相册图片添加成功');
        } else {
            return $this->error(/** LANG */'相册图片添加失败');
        }
    }

    /**
     * 执行更新操作
     * @return Response
     */
    public function update(): Response
    {
        $id = input('id/d', 0);
        $data = $this->request->only([
            'pic_id' => $id,
            'pic_name' => '',
            'galleryPic_desc' => '',
            'galleryPic_pic' => '',
            'is_show/d' => 1,
            'sort_order/d' => 50,
        ], 'post');

        try {
            validate(GalleryPicValidate::class)
                ->scene('update')
                ->check($data);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        $result = $this->galleryPicService->updateGalleryPic($id, $data);
        if ($result) {
            return $this->success(/** LANG */'相册图片更新成功');
        } else {
            return $this->error(/** LANG */'相册图片更新失败');
        }
    }

    /**
     * 更新单个字段
     *
     * @return Response
     */
    public function updateField(): Response
    {
        $id = input('id/d', 0);
        $field = input('field', '');

        if (!in_array($field, ['pic_name', 'sort_order'])) {
            return $this->error('#field 错误');
        }

        $data = [
            'pic_id' => $id,
            $field => input('val'),
        ];

        $this->galleryPicService->updateGalleryPicField($id, $data);

        return $this->success(/** LANG */'更新成功');
    }

    /**
     * 图片上传
     * @return Response
     * @throws \app\common\exceptions\ApiException
     * @throws \think\Exception
     */
    public function uploadImg(): Response
    {
        $gallery_id = input('gallery_id/d', 0);
        if ($gallery_id > 0) {
            $gallery_info = $this->galleryService->getDetail($gallery_id);
            if (!$gallery_info) {
                return $this->error(/** LANG */'不存在此相册');
            }
        }
        if (request()->file('file')) {
            $image = new Image(request()->file('file'), 'gallery');
            $original_img = $image->save();
            $thumb_img = $image->makeThumb(200, 200);
        } else {
            return $this->error(/** LANG */'图片上传错误！');
        }
        if (!$original_img || !$thumb_img) {
            return $this->error(/** LANG */'图片上传错误！');
        }
        $data = [
            'gallery_id' => $gallery_id,
            'pic_ower_id' => Request()->adminUid,
            'pic_url' => $original_img,
            'pic_thumb' => $thumb_img,
            'pic_name' => $image->orgName,
            'add_time' => Time::now(),
            'pic_store_id' => Request()->storeId,
        ];

        $id = $this->galleryPicService->createGalleryPic($data);

        return $this->success([
            'pic_thumb' => $data['pic_thumb'],
            'pic_url' => $data['pic_url'],
            'pic_name' => $data['pic_name'],
            'pic_id' => $id,
        ]);
    }

    /**
     * 删除
     *
     * @return Response
     */
    public function del(): Response
    {
        $id = input('id/d', 0);
        $this->galleryPicService->deleteGalleryPic($id);
        return $this->success(/** LANG */'指定项目已删除');
    }

    /**
     * 批量操作
     *
     * @return Response
     */
    public function batch(): Response
    {
        if (empty(input('ids')) || !is_array(input('ids'))) {
            return $this->error(/** LANG */'未选择项目');
        }

        if (input('type') == 'del') {
            try {
                //批量操作一定要事务
                Db::startTrans();
                foreach (input('ids') as $key => $id) {
                    $id = intval($id);
                    $this->galleryPicService->deleteGalleryPic($id);
                }
                Db::commit();
            } catch (\Exception $exception) {
                Db::rollback();
                throw new ApiException($exception->getMessage());
            }

            return $this->success(/** LANG */'批量操作执行成功！');
        } else {
            return $this->error(/** LANG */'#type 错误');
        }
    }
}
