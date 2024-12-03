<?php

namespace plugin\eagleadmin\app\admin\controller;

use plugin\eagleadmin\app\BaseController;
use plugin\eagleadmin\app\model\EgSystemConfig;

class ConfigController extends BaseController
{
    protected $model;

    public function __construct() {
        $this->model = new EgSystemConfig();
    }
}
