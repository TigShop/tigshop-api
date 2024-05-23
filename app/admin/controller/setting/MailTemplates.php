<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 邮件模板设置
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\admin\controller\setting;

use app\admin\AdminBaseController;
use app\service\api\admin\setting\MailTemplatesService;
use app\validate\setting\MailTemplatesValidate;
use exceptions\ApiException;
use think\App;
use think\exception\ValidateException;
use think\facade\Db;
use think\Response;

/**
 * 邮件模板设置控制器
 */
class MailTemplates extends AdminBaseController
{
    protected MailTemplatesService $mailTemplatesService;

    /**
     * 构造函数
     *
     * @param App $app
     * @param MailTemplatesService $mailTemplatesService
     */
    public function __construct(App $app, MailTemplatesService $mailTemplatesService)
    {
        parent::__construct($app);
        $this->mailTemplatesService = $mailTemplatesService;
        $this->checkAuthor('mailTemplatesManage'); //权限检查
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
            'sort_field' => 'template_id',
            'sort_order' => 'desc',
        ], 'get');

        $filterResult = $this->mailTemplatesService->getFilterResult($filter);
        $total = $this->mailTemplatesService->getFilterCount($filter);

        return $this->success([
            'filter_result' => $filterResult,
            'filter' => $filter,
            'total' => $total,
        ]);
    }

    /**
     * 添加或编辑页面
     *
     * @return Response
     */
    public function detail(): Response
    {
        $id = input('id/d', 0);
        $item = $this->mailTemplatesService->getDetail($id);
        return $this->success([
            'item' => $item,
        ]);
    }

    /**
     * 执行添加操作
     * @return Response
     */
    public function create(): Response
    {
        $data = $this->request->only([
            'template_subject' => '',
            'is_html' => '',
            "template_content" => '',
        ], 'post');

        try {
            validate(MailTemplatesValidate::class)
                ->scene('create')
                ->check($data);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        $result = $this->mailTemplatesService->createMailTemplates($data);
        if ($result) {
            return $this->success(/** LANG */'邮件模板设置添加成功');
        } else {
            return $this->error(/** LANG */'邮件模板设置更新失败');
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
            'template_id' => $id,
            'template_subject' => '',
            'is_html' => '',
            "template_content" => '',
        ], 'post');

        try {
            validate(MailTemplatesValidate::class)
                ->scene('update')
                ->check($data);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        $result = $this->mailTemplatesService->updateMailTemplates($id, $data);
        if ($result) {
            return $this->success(/** LANG */'邮件模板设置更新成功');
        } else {
            return $this->error(/** LANG */'邮件模板设置更新失败');
        }
    }

    /**
     * 删除
     * @return Response
     */
    public function del(): Response
    {
        $id = input('id/d', 0);
        $this->mailTemplatesService->deleteMailTemplates($id);
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
                    $this->mailTemplatesService->deleteMailTemplates($id);
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

    /**
     * 获取所有的邮件模板
     * @return Response
     */
    public function getAllMailTemplates(): Response
    {
        $item =  $this->mailTemplatesService->getAllMailTemplates();
        return $this->success([
            'item' => $item,
        ]);
    }
}
