<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 会员等级
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\adminapi\controller\user;

use app\adminapi\AdminBaseController;
use app\service\api\admin\user\UserRankService;
use think\App;

/**
 * 会员等级控制器
 */
class UserRank extends AdminBaseController
{
    protected UserRankService $userRankService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param UserRankService $userRankService
     */
    public function __construct(App $app, UserRankService $userRankService)
    {
        parent::__construct($app);
        $this->userRankService = $userRankService;
        //$this->checkAuthor('userRankManage'); //权限检查
    }

    /**
     * 列表页面
     *
     * @return \think\Response
     */
    public function list(): \think\Response
    {
        $filter = $this->request->only([
            'keyword' => '',
            'is_page/d' => 0,
            'page' => 1,
            'size' => 15,
            'sort_field' => 'rank_id',
            'sort_order' => 'desc',
        ], 'get');

        $filterResult = $this->userRankService->getFilterResult($filter);
        $total = $this->userRankService->getFilterCount($filter);

        return $this->success([
            'filter_result' => $filterResult,
            'filter' => $filter,
            'total' => $total,
        ]);
    }


    /**
     * 详情
     *
     * @return \think\Response
     */
    public function detail(): \think\Response
    {

        $id = input('id/d', 0);
        $item = $this->userRankService->getDetail($id);
        return $this->success([
            'item' => $item,
        ]);
    }

    /**
     * 执行添加或更新操作
     *
     * @return \think\Response
     */
    public function create(): \think\Response
    {
        $data = $this->request->only([
            'rank_name' => '',
            'min_growth_points/d' => 0,
            'max_growth_points/d' => 0,
            'discount/d' => 0,
            'show_price/d' => 1,
            'rank_type/d' => 2,
            'rank_ico' => '',
            "rank_bg" => '',
        ], 'post');

        $result = $this->userRankService->updateUserRank(0, $data, true);
        if ($result) {
            return $this->success('会员等级添加成功');
        } else {
            return $this->error('会员等级更新失败');
        }
    }

    /**
     * 执行添加或更新操作
     *
     * @return \think\Response
     */
    public function update(): \think\Response
    {
        $id = input('id/d', 0);
        $data = $this->request->only([
            'rank_id' => $id,
            'rank_name' => '',
            'min_growth_points/d' => 0,
            'max_growth_points/d' => 0,
            'discount/d' => 0,
            'show_price/d' => 1,
            'rank_type/d' => 2,
            'rank_ico' => '',
            "rank_bg" => '',
        ], 'post');

        $result = $this->userRankService->updateUserRank($id, $data, false);
        if ($result) {
            return $this->success('会员等级更新成功');
        } else {
            return $this->error('会员等级更新失败');
        }
    }

    /**
     * 更新单个字段
     *
     * @return \think\Response
     */
    public function updateField(): \think\Response
    {
        $id = input('id/d', 0);
        $field = input('field', '');

        if (!in_array($field,
            ['rank_name', 'min_growth_points', 'max_growth_points', 'discount', 'show_price', 'rank_type'])) {
            return $this->error('#field 错误');
        }

        $data = [
            'rank_id' => $id,
            $field => input('val'),
        ];

        $this->userRankService->updateUserRankField($id, $data);

        return $this->success('更新成功');
    }

    /**
     * 删除
     *
     * @return \think\Response
     */
    public function del(): \think\Response
    {
        $id = input('id/d', 0);
        $this->userRankService->deleteUserRank($id);
        return $this->success('指定项目已删除');
    }

    /**
     * 批量操作
     *
     * @return \think\Response
     */
    public function batch(): \think\Response
    {
        if (empty(input('ids')) || !is_array(input('ids'))) {
            return $this->error('未选择项目');
        }

        if (input('type') == 'del') {
            foreach (input('ids') as $key => $id) {
                $id = intval($id);
                $this->userRankService->deleteUserRank($id);
            }
            return $this->success('批量操作执行成功！');
        } else {
            return $this->error('#type 错误');
        }
    }
}
