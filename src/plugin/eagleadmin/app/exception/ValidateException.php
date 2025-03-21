<?php

namespace plugin\eagleadmin\app\exception;

use Exception;
use Webman\Http\Request;
use Webman\Http\Response;
class ValidateException extends Exception
{

    public $msg  = "";
    public $code = 500;

    public $data = [];


    public function __construct($msg, $data=[], $code=-1)
    {
        parent::__construct($msg, $code);

        $this->msg  = $msg;
        $this->data = $data;
        $this->code = $code;

    }

    public function render(Request $request): ?Response
    {
        return json(
            [
                'code' => $this->getCode() ?? 500,
                'msg' => $this->getMessage(),
                'data'=> $this->data,
            ],
            JSON_UNESCAPED_UNICODE
                | JSON_PRETTY_PRINT
                | JSON_UNESCAPED_SLASHES
        );
    }


}