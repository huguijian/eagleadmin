<?php

namespace plugin\eagleadmin\app\controller;
use plugin\eagleadmin\app\BaseController;
use support\Request;
use plugin\eagleadmin\app\model\EgCrontab;
use plugin\eagleadmin\app\model\EgCrontabLog;
use plugin\eagleadmin\app\logic\CrontabLogic;
use support\Response;
class CrontabController extends BaseController
{

    private $cronLogic;
    public function __construct()
    {
        $this->cronLogic = new CrontabLogic();
    }

    /**
     * 查询
     */ 
    public function select(Request $request):Response
    {
        $res = $this->cronLogic->select($request);
        return $this->success($res);
    }

    /**
     * 修改状态
     */
    public function changeStatus(Request $request):Response
    {
        $id = $request->input('id');
        $status = $request->input('status');
        EgCrontab::where('id', $id)->update(['status' => $status]);
        return $this->success([], '修改成功！');
    }


    /**
     * 日志列表
     */ 
    public function logPageList(Request $request):Response
    {
        $res = $this->cronLogic->logPageList($request);
        return $this->success($res, 'ok');
    }


    /**
     * 删除日志
     */
    public function deleteCrontabLog(Request $request):Response
    {
        $id = $request->input('id');
        EgCrontabLog::where('id', $id)->delete();
        return $this->success([], '删除成功！');
    }


    /**
     * 添加
     */
    public function save(Request $request):Response
    {
        $this->cronLogic->insert($request);
        return $this->success([],'');
    }


    /**
     * 删除
     */
    public function destroy(Request $request):Response
    {
        $this->cronLogic->delete($request);
        return $this->success([],'');
    }
    
    /**
     * 执行
     */
    public function run(Request $request):Response
    {
        $id = $request->input('id');
        $res = CrontabLogic::run($id);
        if ($res) {
            return $this->success([],'执行成功！');
        }
        return $this->error('执行失败!');
    }
}