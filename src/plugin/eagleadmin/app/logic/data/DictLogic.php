<?php
namespace plugin\eagleadmin\app\logic\data;
use plugin\eagleadmin\app\logic\ILogic;
use plugin\eagleadmin\app\model\EgDict;

class DictLogic extends ILogic
{
    public function __construct()
    {
        $this->model = new EgDict();
    }

    public function select($request)
    {
        $this->whereArr = [
            ['field'=>'category_id','opt'=>'=','val'=>$request->input('category_id')],
            ['field'=>'dict_name','opt'=>'like','val'=>$request->input('dict_name')],
            ['field'=>'dict_value','opt'=>'like','val'=>$request->input('dict_value')],
            ['field'=>'status','opt'=>'=','val'=>$request->input('status')],
        ];
        return parent::select($request);
    }
}