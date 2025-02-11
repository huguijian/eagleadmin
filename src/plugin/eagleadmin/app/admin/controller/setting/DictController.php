<?php

namespace plugin\eagleadmin\app\admin\controller\setting;

use plugin\eagleadmin\app\BaseController;
use plugin\eagleadmin\app\model\EmsDict;

class DictController extends BaseController
{
    protected $model = null;

    public function __construct()
    {
        $this->model = new EmsDict();
    }
}