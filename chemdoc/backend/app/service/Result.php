<?php
namespace app\service;

class Result
{
    const SUCCESS = 200;
    const ERROR = 0;
    const UNAUTHORIZED = 401;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const VALIDATE_ERROR = 422;
    const SERVER_ERROR = 500;

    public static function success(mixed $data = null, string $msg = '操作成功'): array
    {
        return [
            'code' => self::SUCCESS,
            'msg' => $msg,
            'data' => $data,
            'time' => time(),
        ];
    }

    public static function error(string $msg = '操作失败', int $code = self::ERROR, mixed $data = null): array
    {
        return [
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
            'time' => time(),
        ];
    }

    public static function unauthorized(string $msg = '未授权，请登录'): array
    {
        return self::error($msg, self::UNAUTHORIZED);
    }

    public static function forbidden(string $msg = '无权限访问'): array
    {
        return self::error($msg, self::FORBIDDEN);
    }

    public static function notFound(string $msg = '资源不存在'): array
    {
        return self::error($msg, self::NOT_FOUND);
    }

    public static function validateError(string $msg = '参数验证失败'): array
    {
        return self::error($msg, self::VALIDATE_ERROR);
    }

    public static function serverError(string $msg = '服务器错误'): array
    {
        return self::error($msg, self::SERVER_ERROR);
    }

    public static function paginate(int $total, array $list, int $page = 1, int $pageSize = 20): array
    {
        return self::success([
            'total' => $total,
            'list' => $list,
            'page' => $page,
            'page_size' => $pageSize,
            'total_pages' => ceil($total / $pageSize),
        ]);
    }
}
