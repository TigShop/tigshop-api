<?php

namespace app;

use exceptions\ApiException;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\facade\Env;
use think\Request;
use think\Response;
use Throwable;
use traits\OutputTrait;

/**
 * 应用异常处理类
 */
class ExceptionHandle extends Handle
{
    use OutputTrait;
    /**
     * 不需要记录信息（日志）的异常类列表
     * @var array
     */
    protected $ignoreReport = [
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        DataNotFoundException::class,
        ValidateException::class,
        ApiException::class,
    ];

    /**
     * 记录异常信息（包括日志或者其它方式记录）
     *
     * @access public
     * @param  Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        // 使用内置的方式记录异常日志
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @access public
     * @param \think\Request   $request
     * @param Throwable $e
     * @return Response
     */
    // public function render($request, Throwable $e): Response
    // {
    //     // 添加自定义异常处理机制

    //     // 其他错误交给系统处理
    //     return parent::render($request, $e);
    // }
    public function render(Request $request, Throwable $e): Response
    {
        $app_debug = Env::get('app_debug');
        $message = $app_debug == false ? '请求错误，请稍后再试！' : $e->getMessage();
        // 参数验证错误
        if ($e instanceof ValidateException) {
            return $this->defaultOutput($message,1001);
        }
        if ($e instanceof ApiException) {
            return $this->defaultOutput($message,$e->getCode());
        }
        return $this->fatalOutput($e);
    }
}
