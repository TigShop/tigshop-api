<?php
//**---------------------------------------------------------------------+
//** 通用接口控制器文件 -- 首页
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\index\controller\search;

use app\index\IndexBaseController;
use app\model\product\Product;
use app\service\api\admin\participle\ParticipleService;
use think\App;
use think\Response;

/**
 * 首页控制器
 */
class SearchGuess extends IndexBaseController
{
    /**
     * 构造函数
     *
     * @param App $app
     */
    public function __construct(App $app)
    {
        parent::__construct($app);
        ini_set('memory_limit', '1024M'); //设置PHP运行占用内存，必须
    }

    /**
     * 关键词搜索
     *
     * @return Response
     */
    public function index(): Response
    {
        $keyword = input('keyword');
        // 去掉空格
        $keyword = htmlspecialchars(str_replace(' ', '', trim($keyword)));
        $keyword_list = [];
        if (preg_match('/^[0-9]$/', $keyword)) { // 如果是0-9这种太过简单的则过滤
            $keyword = '';
        }
        if ($keyword != '') {
            $participle = app(ParticipleService::class)->cutForSearch($keyword, true);
            $keyword_where = implode('|', $participle);
            $ks = [];
            $result = Product::where('product_name', 'exp', " REGEXP '$keyword_where' ")->where('is_delete', '=', 0)->field('product_name,keywords,product_id')->select();
            foreach ($result as $key => $value) {
                $value['keywords'] = str_replace(',', ' ', $value['keywords']);
                $keywords_arr = explode(' ', $value['keywords']);
                foreach ($keywords_arr as $k => $val) {
                    //关键词双向匹配
                    if ($val != '' && $val != $keyword && (strpos($keyword, $val) !== false || strpos($val, $keyword) !== false)) {
                        $ks[$val][] = $value['product_id'];
                    }
                }
            }
            $arr = [];
            foreach ($ks as $key => $value) {
                $count = count(array_unique($value));
                $arr[$key] = $count;
            }
            // 排序
            arsort($arr);
            // 获取前10个关键词
            $arr = array_slice($arr, 0, 10, true);
            foreach ($arr as $key => $value) {
                $keyword_list[] = [
                    'type' => 'search',
                    'keyword' => $key,
                    'count' => $value,
                ];
            }
        }
        return $this->success([
            'keyword_list' => $keyword_list,
        ]);
    }

}
