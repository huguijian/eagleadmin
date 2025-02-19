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

    public function select(Request $request): Response
    {
        $this->whereArr = [
            ['field'=>'category_id','opt'=>'=','val'=>$request->input('category_id')],
            ['field'=>'dict_name','opt'=>'like','val'=>$request->input('dict_name')],
            ['field'=>'dict_value','opt'=>'like','val'=>$request->input('dict_value')],
            ['field'=>'status','opt'=>'=','val'=>$request->input('status')],
        ];
        return parent::select($request);
    }

    /**
     * 删除成功
     * @param Request $request
     * @return Response
     */
    public function delete(Request $request): Response
    {
        $id = $request->input('id');
        EgDict::whereIn('id',$id)->forceDelete();
        return $this->success([]);
    }
}
