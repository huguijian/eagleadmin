<?php

namespace plugin\eagleadmin\app\exception;

use Exception;


class ValidateException extends Exception
{

    public $msg  = "";

    public $data = [];

    public $code = 500;


    public function __construct($msg, $data=[], $code=-1)
    {
        parent::__construct($msg, $code);

        $this->msg  = $msg;
        $this->data = $data;
        $this->code = $code;

    }


}