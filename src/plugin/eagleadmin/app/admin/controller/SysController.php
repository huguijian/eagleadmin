<?php

namespace plugin\eagleadmin\app\admin\controller;
use support\Request;
use support\Response;
use plugin\eagleadmin\app\BaseController;
use plugin\eagleadmin\app\model\EgLoginLog;
use plugin\eagleadmin\app\model\EgLog;

class SysController extends BaseController
{
    protected $noNeedAuth = ['loginLog', 'sysLog'];

    public function loginLog(Request $request)
    {
        $id = $request->input('id');
        if ($id) {
            $this->whereArr = ['user_id' => $id];
        }
        $this->model = new EgLoginLog();
        return parent::select($request);
    }

    public function sysLog(Request $request)
    {
        $this->model = new EgLog();
        return parent::select($request);
    }
}
