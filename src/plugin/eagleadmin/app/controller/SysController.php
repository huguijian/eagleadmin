<?php

namespace plugin\eagleadmin\app\controller;

use plugin\eagleadmin\app\logic\SysLogic;
use support\Request;
use support\Response;
use plugin\eagleadmin\app\BaseController;

class SysController extends BaseController
{
    protected $noNeedAuth = ['loginLog', 'sysLog'];


    private $sysLogic;
    public function __construct()
    {
        $this->sysLogic = new SysLogic();
    }
   
    /**
     * 登录日志
     * @param Request $request
     * @return Response
     */
    public function loginLog(Request $request)
    {
        $res = $this->sysLogic->loginLog($request);
        return $this->success($res); 
    }


    /**
     * 操作日志
     * @param Request $request
     * @return Response
     */
    public function sysLog(Request $request)
    {
        
        $res = $this->sysLogic->sysLog($request);
        return $this->success($res);
    }

    /**
     * 删除登录日志
     * @param Request $request
     * @return Response
     */
    public function deleteLoginLog(Request $request)
    {
    
        $this->sysLogic->deleteUserLog($request->input('id'));
        return $this->success([]);
    }

    /**
     * 删除操作日志
     * @param Request $request
     * @return Response
     */
    public function deleteOperLog(Request $request)
    {
        $this->sysLogic->deleteOperLog($request->input('id'));
        return $this->success([]);  
    }
}
