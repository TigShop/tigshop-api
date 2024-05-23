<?php

declare (strict_types = 1);

namespace app;

use think\App;
use think\exception\ValidateException;
use think\Response;
use think\Validate;
use traits\OutputTrait;

/**
 * 控制器基础类
 */
abstract class BaseController
{
    use OutputTrait;

    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [];

    /**
     * 构造方法
     * @access public
     * @param App $app 应用对象
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->request = app(Request::class);
        // 控制器初始化
        $this->initialize();
    }

    // 初始化
    protected function initialize()
    {
    }

    /**
     * 验证数据
     * @access protected
     * @param array $data 数据
     * @param string|array $validate 验证器名或者验证规则数组
     * @param array $message 提示信息
     * @param bool $batch 是否批量验证
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate(array $data, string | array $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        return $v->failException(true)->check($data);
    }

    public function data(array $name, $data = 'param', string | array | null $filter = ''): array
    {
        $item = [];
        foreach ($name as $key => $val) {
            $_key = explode('/', $key);
            $item[$_key[0]] = Request::$data($key, $val, $filter);
            if (is_string($item[$key])) {
                $item[$key] = trim($item[$key]);
            }
        }
        return $item;
    }

    protected function success(string|array $message = '', $error_code = 0): Response
    {
        return $this->output($message, $error_code);
    }

    protected function error(string|array $message = '', $error_code = 1001): Response
    {
        return $this->output($message, $error_code);
    }

    protected function output(string|array $message = '', $error_code = 0): Response
    {
        return $this->defaultOutput($message, $error_code);
    }
}
