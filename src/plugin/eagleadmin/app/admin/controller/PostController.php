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

    public function select(Request $request): Response
    {
        $createTime = $request->input('create_time');
        $this->whereArr = [
            ['field'=>'name', 'opt'=>'like', 'val'=>$request->input('name','')],
            ['field'=>'status', 'opt'=>'=', 'val'=>$request->input('status','')]
        ];

        if ($createTime) {
            array_push($this->whereArr,['field'=>'create_time', 'opt'=>'between', 'val'=>[$createTime[0]??'',$createTime[1]??'']]);
        }
        return parent::select($request);
    }
}
