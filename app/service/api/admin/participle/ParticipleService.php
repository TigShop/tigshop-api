<?php

namespace app\service\api\admin\participle;

use app\service\core\BaseService;
use Fukuball\Jieba\Finalseg;
use Fukuball\Jieba\Jieba;

class ParticipleService extends BaseService
{
    public function __construct()
    {
        Jieba::init(['dict' => 'small']);
        Finalseg::init();
    }

    /**
     * 分词
     * @param string $str
     * @param bool $return_array
     * @return string|array
     */
    public function cutForSearch(string $str, bool $return_array = false): string|array
    {
        $result = Jieba::cutForSearch($str);
        $keywords = array_unique($result);
        if ($return_array) return $keywords;
        return implode(" ", $keywords);
    }
}