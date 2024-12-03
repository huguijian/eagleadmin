<?php

namespace plugin\eagleadmin\app\admin\controller;

use plugin\eagleadmin\app\BaseController;
use plugin\eagleadmin\app\model\EgSystemConfigGroup;

class ConfigGroupController extends BaseController
{
    protected $model;

    public function __construct() {
        $this->model = new EgSystemConfigGroup();
    }
}
