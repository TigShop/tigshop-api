<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- APP版本管理
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\setting;

use app\common\exceptions\ApiException;
use app\common\log\AdminLog;
use app\model\setting\Config;
use app\service\BaseService;
use app\validate\setting\AppVersionValidate;

/**
 * APP版本管理服务类
 */
class AppVersionService extends BaseService
{
    protected AppVersionValidate $appVersionValidate;

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
    public function filterQuery(array $filter): object
    {
        $query = Config::query()->where("code", "app_version");
        // 处理筛选条件
        if (isset($filter['sort_field'], $filter['sort_order']) && !empty($filter['sort_field']) && !empty($filter['sort_order'])) {
            $query->order($filter['sort_field'], $filter['sort_order']);
        }
        return $query;
    }

    /**
     * 获取详情
     *
     * @param int $id
     * @return array
     * @throws ApiException
     */
    public function getDetail(int $id): array
    {
        $result = Config::where('code', "app_version")->find();

        if (!$result) {
            throw new ApiException('APP版本管理不存在');
        }

        return $result->toArray();
    }

    /**
     * 数据配置
     * @param array $data
     * @return array
     */
    public function getCommonVersionData(array $data): array
    {
        $config = [
            'data' => [
                'android_version' => $data["android_version"],
                'ios_version' => $data["ios_version"],
                'ios_link' => $data["ios_link"],
                'android_link' => $data["android_link"],
                'hot_update_link' => $data["hot_update_link"],
                'hot_update_type' => $data["hot_update_type"],
            ],
        ];
        return $config;
    }

    /**
     * 执行APP版本管理添加
     * @param array $data
     * @return int
     * @throws ApiException
     */
    public function createAppVersion(array $data): int
    {
        $app_version = Config::where("code", "app_version")->find();
        $config = $this->getCommonVersionData($data);

        if(!empty($app_version)){
            throw new ApiException(/** LANG */'配置已存在，请勿重复添加！');
        }else{
            $config["code"] = 'app_version';
            // 添加
            $result = Config::create($config);
            AdminLog::add('新增APP版本管理:' . $data['android_version']);
            return $result->getKey();
        }
    }



    /**
     * 执行APP版本管理更新
     * @param int $id
     * @param array $data
     * @return int|bool
     * @throws ApiException
     */
    public function updateAppVersion(int $id, array $data): bool
    {
        $app_version = Config::where("code", "app_version")->find();
        $config = $this->getCommonVersionData($data);

        if(empty($app_version)){
            throw new ApiException(/** LANG */'该配置不存在，请先添加配置！');
        }else{
            $app_version = $app_version->toArray();
            $config["data"]["ios_link"] = !empty($data["ios_link"]) ? $data["ios_link"] : $app_version["data"]["ios_link"];
            $config["data"]["android_link"] = !empty($data["android_link"]) ? $data["android_link"] : $app_version["data"]["android_link"];
            $config["data"]["hot_update_link"] = !empty($data["hot_update_link"]) ? $data["hot_update_link"] : $app_version["data"]["hot_update_link"];
            // 修改
            $result = Config::where('code', "app_version")->save($config);
            AdminLog::add('更新APP版本管理:' . $data['android_version']);
            return $result !== false;
        }
    }

    /**
     * 删除APP版本管理
     *
     * @param int $id
     * @return bool
     */
    public function deleteAppVersion(int $id): bool
    {
        if (!$id) {
            throw new ApiException('#id错误');
        }
        $result = Config::where("code", "app_version")->delete();

        return $result !== false;
    }
}
