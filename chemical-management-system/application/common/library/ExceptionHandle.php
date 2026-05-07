<?php

namespace app\common\library;

use think\exception\Handle;
use think\exception\HttpException;
use think\exception\ValidateException;
use think\Response;
use Throwable;

class ExceptionHandle extends Handle
{
    protected $ignoreReport = [
        '\\think\\exception\\HttpException',
    ];

    public function render($request, Throwable $e): Response
    {
        if ($e instanceof ValidateException) {
            return json([
                'code' => 0,
                'msg'  => $e->getError(),
            ], 422);
        }

        if ($e instanceof HttpException) {
            return json([
                'code' => $e->getStatusCode(),
                'msg'  => $e->getMessage(),
            ], $e->getStatusCode());
        }

        if (config('app.app_debug')) {
            return json([
                'code' => 0,
                'msg'  => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }

        return json([
            'code' => 0,
            'msg'  => '服务器错误，请稍后重试',
        ], 500);
    }

    public function report(Throwable $e): void
    {
        if (!$this->isIgnoreReport($e)) {
            $log = "[{$e->getCode()}] {$e->getMessage()} [{$e->getFile()}:{$e->getLine()}]";
            log_write($log, 'error');
        }
    }

    protected function isIgnoreReport(Throwable $e): bool
    {
        foreach ($this->ignoreReport as $class) {
            if ($e instanceof $class) {
                return true;
            }
        }
        return false;
    }
}
