<?php

namespace plugin\eagleadmin\app;

/**
 * @author huguijian <18616725397@163.com>
 */
class BaseController
{
    use Crud;
    /**
     * success
     * 成功返回请求结果
     * @param array $data
     * @param string|null $msg
     */
    public function success($data = [], ?string $msg = "success",$code=0)
    {
        $data = ($data==null) ? [] : $data;
        $rdata = [
            "code" => $code,
            "data" => $data,
            "msg"  => $msg
        ];
        return json($rdata);
    }

    /**
     * error
     * 业务相关错误结果返回
     */
    public function error( $msg = "",$code = -1, $data = [])
    {
        $rdata = [
            "code" => $code,
            "data" => $data,
            "msg"  => $msg
        ];
        return json($rdata);
    }
}
