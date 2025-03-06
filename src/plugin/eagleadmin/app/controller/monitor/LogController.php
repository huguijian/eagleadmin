<?php

namespace plugin\eagleadmin\app\controller\monitor;

use plugin\eagleadmin\app\logic\monitor\LogLogic;
use support\Request;
use support\Response;
use plugin\eagleadmin\app\BaseController;

class LogController extends BaseController
{
    protected $noNeedAuth = ['loginLog', 'sysLog'];


    private $logLogic;
    public function __construct()
    {
        $this->logLogic = new LogLogic();
    }
   
    /**
     * 登录日志
     * @param Request $request
     * @return Response
     */
    public function loginLog(Request $request)
    {
        $res = $this->logLogic->loginLog($request);
        return $this->success($res); 
    }


    /**
     * 操作日志
     * @param Request $request
     * @return Response
     */
    public function sysLog(Request $request)
    {
        
        $res = $this->logLogic->sysLog($request);
        return $this->success($res);
    }

    /**
     * 删除登录日志
     * @param Request $request
     * @return Response
     */
    public function deleteLoginLog(Request $request)
    {
    
        $this->logLogic->deleteUserLog($request->input('id'));
        return $this->success([]);
    }

    /**
     * 删除操作日志
     * @param Request $request
     * @return Response
     */
    public function deleteOperLog(Request $request)
    {
        $this->logLogic->deleteOperLog($request->input('id'));
        return $this->success([]);  
    }
}
