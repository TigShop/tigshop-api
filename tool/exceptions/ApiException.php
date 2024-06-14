<?php

namespace exceptions;

use app\constant\ResponseCode;
use think\Exception;

class ApiException extends Exception
{
    protected $errorData = [];

    public function __construct($message = 'An error occurred', $code = ResponseCode::ERROR, $errorData = [])
    {
        $this->message = $message;
        $this->code = $code;
        $this->errorData = $errorData;
        parent::__construct($this->message, $this->code);
    }

    public function getErrorData()
    {
        return $this->errorData;
    }
}
