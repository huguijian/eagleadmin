<?php

namespace plugin\eagleadmin\app\controller\config;

use plugin\eagleadmin\app\BaseController;
use plugin\eagleadmin\app\logic\config\ConfigGroupLogic;
use support\Request;
class ConfigGroupController extends BaseController
{
    private $configGroupLogic;

    public function __construct() 
    {
        $this->configGroupLogic = new ConfigGroupLogic();
    }


    /**
     * 配置组列表
     * @param \support\Request $request
     */
    public function select(Request $request)
    {
        $res = $this->configGroupLogic->select($request);
        return $this->success($res,);
    }

    /**
     * 更新配置组
     * @param \support\Request $request
     */
    public function update(Request $request)
    {
        $res = $this->configGroupLogic->update($request);
        return $this->success($res,"更新成功");
    }

    /**
     * 添加配置组
     * @param \support\Request $request
     */
    public function insert(Request $request)
    {
        $res = $this->configGroupLogic->insert($request);
        return $this->success($res,"添加成功");
    }


    /**
     * 删除配置组
     * @param \support\Request $request
     */
    public function delete(Request $request)
    {
        $res = $this->configGroupLogic->delete($request);
        return $this->success($res,"删除成功");
    }
}
