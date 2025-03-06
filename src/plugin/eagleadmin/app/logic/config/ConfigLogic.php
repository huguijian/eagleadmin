<?php
namespace plugin\eagleadmin\app\logic\config;
use plugin\eagleadmin\app\logic\ILogic;
use plugin\eagleadmin\app\model\EgSystemConfig;

class ConfigLogic extends ILogic
{
    public function __construct()
    {
        $this->model = new EgSystemConfig();
    }

    public function select($request)
    {
        $this->whereArr = [
            ['opt'=>'=', 'field'=>'group_id', 'val'=>$request->input('group_id')],
            ['opt'=>'like', 'field'=>'name', 'val'=>'%'.$request->input('name').'%'],
            ['opt'=>'like', 'field'=>'key', 'val'=>'%'.$request->input('key').'%'],
        ];
        return parent::select($request);
    }

    public function batchUpdate($request)
    {
        $params = $request->all();
        foreach ($params['config'] as $value) {
            $this->model->where('id', $value['id'])->update(['value'=>$value['value']]);
        }
        return true;
    }
}