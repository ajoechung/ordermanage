<?php
namespace app\exception;

use app\service\Result;
use think\exception\Handle;
use think\exception\ValidateException;
use think\Response;
use Throwable;

class ExceptionHandle extends Handle
{
    protected array $ignoreReport = [
        \think\exception\HttpException::class,
        \think\exception\HttpResponseException::class,
        \think\exception\ValidateException::class,
    ];

    public function render($request, Throwable $e): Response
    {
        if ($e instanceof ValidateException) {
            return json(Result::validateError($e->getMessage()), 422);
        }

        if ($e instanceof \think\exception\HttpResponseException) {
            return $e->getResponse();
        }

        if ($e instanceof \think\exception\HttpException) {
            return json(Result::error($e->getMessage(), $e->getStatusCode()), $e->getStatusCode());
        }

        if (APP_DEBUG) {
            return parent::render($request, $e);
        }

        $this->logException($e);

        $message = '服务器错误，请稍后重试';
        
        return json(Result::serverError($message), 500);
    }

    protected function logException(Throwable $e): void
    {
        $log = "[{$e->getCode()}] {$e->getMessage()}" . PHP_EOL;
        $log .= "File: {$e->getFile()}:{$e->getLine()}" . PHP_EOL;
        $log .= "Trace: " . PHP_EOL . $e->getTraceAsString();
        
        trace($log, 'error');
    }
}
