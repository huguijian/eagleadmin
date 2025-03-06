<?php

namespace plugin\eagleadmin\app\controller\config;

use plugin\eagleadmin\app\BaseController;
use plugin\eagleadmin\app\logic\config\ConfigLogic;
use support\Request;

class ConfigController extends BaseController
{

    private $configLogic;
    public function __construct() 
    {
        $this->configLogic = new ConfigLogic();
    }

    public function select(Request $request)
    {
        $res = $this->configLogic->select($request) ;
        return $this->success($res);
    }


    public function insert(Request $request)
    {
        $res = $this->configLogic->insert($request) ;
        return $this->success($res);
    }


    public function delete(Request $request)
    {
        $this->configLogic->delete($request);
        return $this->success([],'删除成功');
    }


    public function update(Request $request)
    {
        $this->configLogic->update($request);
        return $this->success([],'修改成功');
    }


    public function batchUpdate(Request $request)
    {
        $this->configLogic->batchUpdate($request);
        return $this->success([],'修改成功');
    }
}
