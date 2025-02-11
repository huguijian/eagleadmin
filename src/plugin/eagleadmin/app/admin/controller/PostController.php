<?php

namespace plugin\eagleadmin\app\admin\controller;

use plugin\eagleadmin\app\BaseController;
use plugin\eagleadmin\app\model\EgPost;
use support\Request;
use support\Db;
use support\Response;

/**
 * 岗位管理
 */
class PostController extends BaseController
{
    protected $model;

    public function __construct() {
        $this->model = new EgPost();
    }
}
