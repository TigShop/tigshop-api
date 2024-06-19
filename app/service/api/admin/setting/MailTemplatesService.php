<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 邮件模板设置
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\api\admin\setting;

use app\model\setting\MailTemplates;
use app\service\core\BaseService;
use exceptions\ApiException;
use log\AdminLog;
use utils\Time;

/**
 * 邮件模板设置服务类
 */
class MailTemplatesService extends BaseService
{
    public function __construct()
    {
    }

    /**
     * 获取筛选结果
     *
     * @param array $filter
     * @return array
     */
    public function getFilterResult(array $filter): array
    {
        $query = $this->filterQuery($filter)->append(["template_name"]);
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
    public function filterQuery(array $filter): object
    {
        $query = MailTemplates::query();
        // 处理筛选条件
        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $query->where('template_subject', 'like', '%' . $filter['keyword'] . '%');
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
     * @return MailTemplates
     * @throws ApiException
     */
    public function getDetail(int $id): MailTemplates
    {
        $result = MailTemplates::where('template_id', $id)->append(["template_name"])->find();

        if (!$result) {
            throw new ApiException(/** LANG */'邮件模板设置不存在');
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
        return MailTemplates::where('template_id', $id)->value('template_subject');
    }

    /**
     * 添加邮件模板设置
     * @param array $data
     * @return int
     */
    public function createMailTemplates(array $data): int
    {
        $result = MailTemplates::create($data);
        AdminLog::add('新增邮件模板设置:' . $data['template_subject']);
        return $result->getKey();
    }

    /**
     * 执行邮件模板设置更新
     *
     * @param int $id
     * @param array $data
     * @return bool
     * @throws ApiException
     */
    public function updateMailTemplates(int $id, array $data): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $arr = [
            'template_subject' => $data['template_subject'],
            'is_html' => $data['is_html'],
            'template_content' => $data['template_content'],
            "last_modify" => Time::now(),
            "type" => "template",
        ];
        $result = MailTemplates::where('template_id', $id)->save($arr);
        AdminLog::add('更新邮件模板设置:' . $this->getName($id));
        return $result !== false;
    }

    /**
     * 删除邮件模板设置
     *
     * @param int $id
     * @return bool
     */
    public function deleteMailTemplates(int $id): bool
    {
        if (!$id) {
            throw new ApiException(/** LANG */'#id错误');
        }
        $get_name = $this->getName($id);
        $result = MailTemplates::destroy($id);

        if ($result) {
            AdminLog::add('删除邮件模板设置:' . $get_name);
        }

        return $result !== false;
    }

    /**
     * 获取所有邮件模板设置
     * @return array
     */
    public function getAllMailTemplates(): array
    {
        return MailTemplates::query()->append(["template_name"])->select()->toArray();
    }
}
