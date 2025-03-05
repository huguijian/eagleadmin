<?php

namespace plugin\eagleadmin\app\controller;

use plugin\eagleadmin\app\BaseController;
use plugin\eagleadmin\app\logic\ConfigGroupLogic;
use support\Request;
class ConfigGroupController extends BaseController
{
    private $configGroupLogic;

    public function __construct() 
    {
        $this->configGroupLogic = new ConfigGroupLogic();
    }


    public function select(Request $request)
    {
        $res = $this->configGroupLogic->select($request);
        return $this->success($res,);
    }
}
