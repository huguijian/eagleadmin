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

    /**
     * 获取配置列表
     * @param mixed $request
     * @return array
     */
    public function select($request)
    {
        $this->whereArr = [
            ['opt'=>'=', 'field'=>'group_id', 'val'=>$request->input('group_id')],
            ['opt'=>'like', 'field'=>'name', 'val'=>'%'.$request->input('name').'%'],
            ['opt'=>'like', 'field'=>'key', 'val'=>'%'.$request->input('key').'%'],
        ];
        return parent::select($request);
    }

    /**
     * 批量更新
     * @param mixed $request
     * @return bool
     */
    public function batchUpdate($request)
    {
        $params = $request->all();
        foreach ($params['config'] as $value) {
            $this->model->where('id', $value['id'])->update(['value'=>$value['value']]);
        }
        return true;
    }
}