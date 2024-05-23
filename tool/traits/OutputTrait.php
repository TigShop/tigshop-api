<?php

namespace traits;

use think\facade\Env;
use think\Response;

trait OutputTrait
{
    // 普通逻辑层的格式输出
    protected function defaultOutput(string | array $message = '', int $error_code = 0): Response
    {
        if (is_array($message)) {
            $data = $message;
            $data['errcode'] = $error_code;
            $data['message'] = $data['message'] ?? '';
            return json([
                'code' => 0,
                'msg' => 'ok',
                'data' => $data,
            ]);
        } else {
            return json([
                'code' => 0,
                'msg' => 'ok',
                'data' => [
                    'errcode' => $error_code,
                    'message' => $message,
                ],
            ]);
        }
    }
    // 页面层的严重错误抛出格式，如代码错误、数据库错误等
    protected function fatalOutput($exception): Response
    {
        $app_debug = Env::get('app_debug');
        $message = $app_debug == false ? '请求错误，请稍后再试！' : $exception->getMessage();
        $code = $exception->getCode() > 0 ? $exception->getCode() : 500;
        if (!in_array($code, [200, 400, 401, 403, 500])) {
            $code = 500;
        }
        $arr = [
            'code' => $code,
            'msg' => $message,
            'data' => [
                'errcode' => $code,
                'message' => $message,
            ],
        ];
        if ($app_debug == true) {
            $arr['data']['file'] = $exception->getFile();
            $arr['data']['line'] = $exception->getLine();
            $arr['data']['trace'] = $exception->getTrace();
        }
        return json($arr, $code);
    }
}
