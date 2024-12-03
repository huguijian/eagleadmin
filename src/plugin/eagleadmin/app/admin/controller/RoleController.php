<?php

namespace plugin\eagleadmin\app\admin\controller;

use plugin\eagleadmin\app\BaseController;
use support\Request;
use support\Db;
use support\Response;
use plugin\eagleadmin\app\admin\logic\UserLogic;
use plugin\eagleadmin\app\model\EgDepartment;
use plugin\eagleadmin\app\model\EgRole;
use plugin\eagleadmin\app\UploadValidator;
use plugin\eagleadmin\app\service\CommonService;
use plugin\eagleadmin\app\model\EgUser;

class RoleController extends BaseController
{
    protected $model;

    public function __construct() {
        $this->model = new EgRole();
    }
}
