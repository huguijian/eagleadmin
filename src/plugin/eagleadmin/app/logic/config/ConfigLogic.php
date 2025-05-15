<?php
namespace plugin\eagleadmin\app\logic\config;
use plugin\eagleadmin\app\logic\ILogic;
use plugin\eagleadmin\app\model\EgSystemConfig;
use plugin\eagleadmin\app\model\EgSystemConfigGroup;
use plugin\eagleadmin\app\exception\BusinessException;
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
        $this->callBack = function($data) {
            collect($data)->map(function($item) {
                $item['config_select_data'] = json_decode($item['config_select_data'], true);
                return $item;
            });
            return $data;

        };
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

    /**
     * 获取配置分组
     * @param mixed $groupCode
     * @throws \plugin\eagleadmin\app\exception\BusinessException
     * @return array<\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Collection|\support\Model>|\Illuminate\Database\Eloquent\Collection
     */
    public function getConfigGroup($groupCode=[])
    {
        $groupId = EgSystemConfigGroup::whereIn('code', $groupCode)->pluck('id');
        $groupId = $groupId->toArray();
        if (!$groupId) {
            throw new BusinessException('配置分组不存在');
        }
        return $this->model->with('groupInfo')->whereIn('group_id', $groupId)->get()->toArray();
    }

    /**
     * 获取配置
     * @param mixed $groupCode
     * @return array
     */
    public function getConfig($groupCode=[])
    {
        $configList = $this->getConfigGroup($groupCode);
        $configMap = [];
        foreach ($configList as $config) {
            $configMap[$config['group_info']['code']][$config['key']] = $config['value'];
        }
        return $configMap;
    }
}