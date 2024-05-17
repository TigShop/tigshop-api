<?php

namespace app\common\exceptions;

use think\Exception;

class ApiException extends Exception
{
    protected $code = 0;
    protected $message = 'An error occurred';
    protected $errorData = [];

    public function __construct($message = null, $code = null, $errorData = [])
    {
        $this->message = $message ?: $this->message;
        $this->code = $code ? $code : 1001;
        $this->errorData = $errorData;
        parent::__construct($this->message, $this->code);
    }

    public function getErrorData()
    {
        return $this->errorData;
    }
}
