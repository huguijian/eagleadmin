<?php

namespace plugin\eagleadmin\app\admin\controller;

use plugin\eagleadmin\app\BaseController;
use plugin\eagleadmin\app\model\EgDict;
use support\Request;
use support\Db;
use support\Response;

class DictController extends BaseController
{
    protected $model;

    public function __construct() {
        $this->model = new EgDict();
    }
}
