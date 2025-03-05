<?php

namespace plugin\eagleadmin\app\controller;

use plugin\eagleadmin\app\BaseController;
use plugin\eagleadmin\app\logic\ConfigLogic;

class ConfigController extends BaseController
{

    private $configLogic;
    public function __construct() 
    {
        $this->configLogic = new ConfigLogic();
    }
}
