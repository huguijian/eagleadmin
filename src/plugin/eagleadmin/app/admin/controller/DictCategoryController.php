<?php

namespace plugin\eagleadmin\app\admin\controller;

use plugin\eagleadmin\app\BaseController;
use plugin\eagleadmin\app\model\EgDictCategory;
use support\Request;
use support\Db;
use support\Response;

class DictCategoryController extends BaseController
{
    protected $model;

    public function __construct() {
        $this->model = new EgDictCategory();
    }

    public function data(Request $request)
    {
        $code = $request->input('code');
        $category = EgDictCategory::where('code', $code)->first();
        $data = $category->dict ?? [];
        return $this->success($data);
    }
}
