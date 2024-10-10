<?php

namespace plugin\eagleadmin\app\exception;

use Exception;
use Webman\Http\Request;
use Webman\Http\Response;

class BusinessException extends Exception
{

    public $msg  = "";

    public $data = [];

    public $code = -1;


    public function __construct($msg, $data=[], $code=-1)
    {
        parent::__construct($msg, $code);

        $this->msg  = $msg;
        $this->data = $data;
        $this->code = $code;
    }

    public function getData()
    {
        return $this->data;
    }

    public function render(Request $request): ?Response
    {
        return json(
            [
                'code' => $this->getCode() ?? 500,
                'msg' => $this->getMessage(),
                'data' => $this->getData(),
            ],
            JSON_UNESCAPED_UNICODE
                | JSON_PRETTY_PRINT
                | JSON_UNESCAPED_SLASHES
        );
    }
}
