<?php

namespace plugin\eagleadmin\app\controller\config;

use plugin\eagleadmin\app\BaseController;
use plugin\eagleadmin\app\logic\config\ConfigLogic;
use support\Request;

class ConfigController extends BaseController
{

    private $configLogic;
    public function __construct() 
    {
        $this->configLogic = new ConfigLogic();
    }


    /**
     * 配置项列表
     * @param \support\Request $request
     */
    public function select(Request $request)
    {
        $res = $this->configLogic->select($request) ;
        return $this->success($res);
    }


    /**
     * 新增配置项
     * @param \support\Request $request
     */
    public function insert(Request $request)
    {
        $res = $this->configLogic->insert($request) ;
        return $this->success($res);
    }



    /**
     * 删除配置项
     * @param \support\Request $request
     */
    public function delete(Request $request)
    {
        $this->configLogic->delete($request);
        return $this->success([],'删除成功');
    }


    /** 
     * 编辑配置项
     * @param \support\Request $request
     */
    public function update(Request $request)
    {
        $this->configLogic->update($request);
        return $this->success([],'修改成功');
    }


    /**
     * 批量修改配置项
     * @param \support\Request $request
     */
    public function batchUpdate(Request $request)
    {
        $this->configLogic->batchUpdate($request);
        return $this->success([],'修改成功');
    }


    /**
     * 清除缓存
     * @param \support\Request $request
     */
    public function clearAllCache(Request $request)
    {
        return $this->success([],'清除成功');
    }
}
