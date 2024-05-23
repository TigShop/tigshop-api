<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 相册
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\admin\controller\setting;

use app\admin\AdminBaseController;
use app\service\api\admin\setting\GalleryService;
use app\validate\setting\GalleryValidate;
use exceptions\ApiException;
use think\App;
use think\exception\ValidateException;
use think\facade\Db;
use think\Response;

/**
 * 相册控制器
 */
class Gallery extends AdminBaseController
{
    protected GalleryService $galleryService;
    protected $sort = 2; //排序

    /**
     * 构造函数
     *
     * @param App $app
     * @param GalleryService $galleryService
     */
    public function __construct(App $app, GalleryService $galleryService)
    {
        parent::__construct($app);
        $this->galleryService = $galleryService;
        $this->checkAuthor('galleryManage'); //权限检查
    }

    /**
     * 列表页面
     *
     * @return Response
     */
    public function list(): Response
    {
        $filter = $this->request->only([
            'gallery_id/d' => 0,
            'page/d' => 1,
            'size/d' => 99,
            'sort_field' => 'gallery_id',
            'sort_order' => 'asc',
        ], 'get');
        $filterResult = $this->galleryService->getFilterResult($filter);
        $total = $this->galleryService->getFilterCount($filter);

        return $this->success([
            'filter_result' => $filterResult,
            'filter' => $filter,
            'size' => $filter['size'],
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
        $item = $this->galleryService->getDetail($id);
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
            'gallery_name' => '',
            'parent_id' => '',
            'gallery_sort' => 50,
        ], 'post');

        try {
            validate(GalleryValidate::class)
                ->scene('create')
                ->check($data);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        $result = $this->galleryService->createGallery($data);
        if ($result) {
            return $this->success(/** LANG */'相册添加成功');
        } else {
            return $this->error(/** LANG */'相册添加失败');
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
            'gallery_id' => $id,
            'gallery_name' => '',
            'parent_id' => '',
            'gallery_sort' => 50,
        ], 'post');

        try {
            validate(GalleryValidate::class)
                ->scene('update')
                ->check($data);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        $result = $this->galleryService->updateGallery($id, $data);
        if ($result) {
            return $this->success(/** LANG */'相册更新成功');
        } else {
            return $this->error(/** LANG */'相册更新失败');
        }
    }

    /**
     * 更新单个字段
     * @return Response
     */
    public function updateField(): Response
    {
        $id = input('id/d', 0);
        $field = input('field', '');

        if (!in_array($field, ['gallery_name', 'is_show', 'sort_order'])) {
            return $this->error(/** LANG */'#field 错误');
        }

        $data = [
            'gallery_id' => $id,
            $field => input('val'),
        ];

        $this->galleryService->updateGalleryField($id, $data);

        return $this->success(/** LANG */'更新成功');
    }

    /**
     * 删除
     * @return Response
     */
    public function del(): Response
    {
        $id = input('id/d', 0);
        $this->galleryService->deleteGallery($id);
        return $this->success(/** LANG */'指定项目已删除');
    }

    /**
     * 批量操作
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
                    $this->galleryService->deleteGallery($id);
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
