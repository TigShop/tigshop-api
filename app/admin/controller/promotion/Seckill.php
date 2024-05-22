<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 秒杀活动
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\admin\controller\promotion;

use app\admin\AdminBaseController;
use app\common\exceptions\ApiException;
use app\service\api\admin\promotion\SeckillService;
use app\validate\promotion\SeckillValidate;
use think\App;
use think\exception\ValidateException;
use think\facade\Db;
use think\Response;

/**
 * 秒杀活动控制器
 */
class Seckill extends AdminBaseController
{
    protected SeckillService $seckillService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param SeckillService $seckillService
     */
    public function __construct(App $app, SeckillService $seckillService)
    {
        parent::__construct($app);
        $this->seckillService = $seckillService;
        $this->checkAuthor('seckillManage'); //权限检查
    }

    /**
     * 列表页面
     *
     * @return Response
     */
    public function list(): Response
    {
        $filter = $this->request->only([
            'keyword' => '',
            'page/d' => 1,
            'size/d' => 15,
            'sort_field' => 'seckill_id',
            'sort_order' => 'desc',
        ], 'get');

        $filterResult = $this->seckillService->getFilterResult($filter);
        $total = $this->seckillService->getFilterCount($filter);

        return $this->success([
            'filter_result' => $filterResult,
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
        $item = $this->seckillService->getDetail($id);
        return $this->success([
            'item' => $item,
        ]);
    }

    /**
     * 请求数据
     * @return Response
     */
    public function requestData(): array
    {
        $data = $this->request->only([
            'seckill_name' => '',
            'seckill_start_time' => "",
            "seckill_end_time" => "",
            "seckill_limit_num" => "",
            "product_id/d" => 0,
            "seckill_item" => [],
        ], 'post');

        return $data;
    }

    /**
     * 添加
     * @return Response
     * @throws \app\common\exceptions\ApiException
     */
    public function create(): Response
    {
        $data = $this->requestData();

        try {
            validate(SeckillValidate::class)
                ->scene('create')
                ->check($data);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        $result = $this->seckillService->createSeckill($data);
        if ($result) {
            return $this->success(/** LANG */'秒杀活动添加成功');
        } else {
            return $this->error(/** LANG */'秒杀活动添加失败');
        }
    }

    /**
     * 执行更新操作
     * @return Response
     */
    public function update(): Response
    {
        $id = input('id/d', 0);
        $data = $this->requestData();
        $data["seckill_id"] = $id;
        try {
            validate(SeckillValidate::class)
                ->scene('update')
                ->check($data);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        $result = $this->seckillService->updateSeckill($id, $data);
        if ($result) {
            return $this->success(/** LANG */'秒杀活动更新成功');
        } else {
            return $this->error(/** LANG */'秒杀活动更新失败');
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

        if (!in_array($field, ['seckill_name', 'sort_order', 'is_show'])) {
            return $this->error(/** LANG */'#field 错误');
        }

        $data = [
            'seckill_id' => $id,
            $field => input('val'),
        ];

        $this->seckillService->updateSeckillField($id, $data);

        return $this->success(/** LANG */'更新成功');
    }

    /**
     * 删除
     *
     * @return Response
     */
    public function del(): Response
    {
        $id = input('id/d', 0);
        $this->seckillService->deleteSeckill($id);
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
                    $this->seckillService->deleteSeckill($id);
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
