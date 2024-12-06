<?php

namespace plugin\eagleadmin\app\admin\controller;

use plugin\eagleadmin\app\BaseController;
use support\Request;
use support\Db;
use support\Response;
use plugin\eagleadmin\app\admin\logic\UserLogic;
use plugin\eagleadmin\app\model\EgDepartment;
use plugin\eagleadmin\app\UploadValidator;
use plugin\eagleadmin\app\service\CommonService;
use plugin\eagleadmin\app\model\EgUser;
use plugin\eagleadmin\utils\Helper;

class DeptController extends BaseController
{
    protected $model;

    public function __construct() {
        $this->model = new EgDepartment();
    }

    public function select(Request $request) :Response
    {
        $this->callBack = function($data) {
            $data = collect($data)->map(function($item){
                    $item['label'] = $item['name'];
                    $item['value'] = $item['id'];
                    return $item;
                })
                ->toArray();
            return Helper::makeTree($data);
        };
        return parent::select($request);
    }
}
