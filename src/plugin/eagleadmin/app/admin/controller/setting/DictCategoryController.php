<?php

namespace plugin\eagleadmin\app\admin\controller\setting;

use plugin\eagleadmin\app\BaseController;
use plugin\eagleadmin\app\model\EmsDictCategory;

class DictCategoryController extends BaseController
{
    protected $model = null;

    public function __construct()
    {
        $this->model = new EmsDictCategory();
    }
}