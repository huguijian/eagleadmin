<?php

namespace plugin\eagleadmin\app\admin\controller;

use plugin\eagleadmin\app\BaseController;
use plugin\eagleadmin\app\logic\ConfigGroupLogic;

class ConfigGroupController extends BaseController
{
    private $configGroupLogic;

    public function __construct() 
    {
        $this->configGroupLogic = new ConfigGroupLogic();
    }
}
