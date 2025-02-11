<?php

namespace plugin\eagleadmin\app\admin\controller;
use plugin\eagleadmin\app\model\EgOperateLog;
use support\Request;
use support\Response;
use plugin\eagleadmin\app\BaseController;
use plugin\eagleadmin\app\model\EgLoginLog;

class SysController extends BaseController
{
    protected $noNeedAuth = ['loginLog', 'sysLog'];


    /**
     * 登录日志
     * @param Request $request
     * @return Response
     */
    public function loginLog(Request $request)
    {
        $id = $request->input('id');
        if ($id) {
            $this->whereArr = ['user_id' => $id];
        }
        $this->model = new EgLoginLog();
        return parent::select($request);
    }


    /**
     * 操作日志
     * @param Request $request
     * @return Response
     */
    public function sysLog(Request $request)
    {
        $this->model = new EgOperateLog();
        return parent::select($request);
    }
}
