<?php

namespace plugin\eagleadmin\app\controller\tool;
use plugin\eagleadmin\app\BaseController;
use support\Request;
use plugin\eagleadmin\app\model\EgCrontab;
use plugin\eagleadmin\app\model\EgCrontabLog;
use plugin\eagleadmin\app\logic\tool\CrontabLogic;
use support\Response;
class CrontabController extends BaseController
{

    private $cronLogic;
    public function __construct()
    {
        $this->cronLogic = new CrontabLogic();
    }

    /**
     * 任务列表
     * @param \support\Request $request
     * @return \support\Response
     */
    public function select(Request $request):Response
    {
        $res = $this->cronLogic->select($request);
        return $this->success($res);
    }

    /**
     * 修改状态
     * @param \support\Request $request
     * @return \support\Response
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
     * @param \support\Request $request
     * @return \support\Response
     */
    public function logPageList(Request $request):Response
    {
        $res = $this->cronLogic->logPageList($request);
        return $this->success($res, 'ok');
    }


    /**
     * 删除日志
     * @param \support\Request $request
     * @return \support\Response
     */
    public function deleteCrontabLog(Request $request):Response
    {
        $id = $request->input('id');
        EgCrontabLog::where('id', $id)->delete();
        return $this->success([], '删除成功！');
    }


    /**
     * 添加定时任务
     * @param \support\Request $request
     * @return \support\Response
     */
    public function save(Request $request):Response
    {
        $this->cronLogic->insert($request);
        return $this->success([],'');
    }


    /**
     * 修改定时任务
     * @param \support\Request $request
     * @return \support\Response
     */
    public function update(Request $request):Response
    {
        $res = $this->cronLogic->update($request);
        return $this->success($res,'更新成功！');
    }


    /**
     * 删除定时任务
     * @param \support\Request $request
     * @return \support\Response
     */
    public function destroy(Request $request):Response
    {
        $this->cronLogic->delete($request);
        return $this->success([],'');
    }
    
    /**
     * 执行任务
     * @param \support\Request $request
     * @return \support\Response
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