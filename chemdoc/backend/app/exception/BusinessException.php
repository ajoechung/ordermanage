<?php
namespace app\exception;

use Exception;

class BusinessException extends Exception
{
    protected int $errorCode;
    protected $data;

    public function __construct(string $message = '', int $errorCode = 0, $data = null)
    {
        parent::__construct($message);
        $this->errorCode = $errorCode;
        $this->data = $data;
    }

    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    public function getData()
    {
        return $this->data;
    }
}
