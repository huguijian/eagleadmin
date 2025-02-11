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

    protected $noNeedAuth = ['dictAll'];

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

    public function dictAll(Request $request)
    {
        $dcs = EgDictCategory::where('status', 1)->get();
        $res = [];
        foreach($dcs as $dc) {
            $dict = optional($dc['dict'])->toArray();
            foreach($dict as $key => $val) {
                $dict[$key]['label'] = $val['dict_name'];
                $dict[$key]['value'] = $val['dict_value'];
            }
            $res[$dc['code']] = $dict;
        }
        return $this->success($res, '查询成功！');
    }
}
