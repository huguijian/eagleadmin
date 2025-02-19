<?php

namespace plugin\eagleadmin\app\admin\controller;

use plugin\eagleadmin\app\BaseController;
use support\Request;
use plugin\eagleadmin\app\model\EgCrontab;
use plugin\eagleadmin\app\model\EgCrontabLog;
use plugin\eagleadmin\app\admin\logic\CrontabLogic;
use support\Response;
class CrontabController extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new EgCrontab();
    }


    /**
     * 查询
     */ 
    public function select(Request $request):Response
    {
        $this->whereArr = [
            ['opt'=>'=','field'=>'status','val'=>$request->get('status')],
            ['opt'=>'like','field'=>'name','val'=>$request->get('name')],
            ['opt'=>'=','field'=>'type','val'=>$request->get('type')]
        ];
        return parent::select($request);
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
        $this->model = new EgCrontabLog();
        [$where, $pageSize, $order] = $this->selectInput($request);
        $order = $this->orderBy ?? 'id,desc';
        $this->whereArr = [
            ['opt'=>'=','field'=>'crontab_id','val'=>$request->get('crontab_id')]
        ];
        $model = $this->selectMap($where,$order);
        if ($this->pageSize == -1) { // 值为-1表示不分页
            $list = $model->get() ?? [];
        } else {
            $pageSize = $this->pageSize > 0 ? $this->pageSize : $pageSize;
            $paginator = $model->paginate($pageSize);
            $list = $paginator->items() ?? [];
            $res['total'] = $paginator->total();
        }
        $res['items'] = $list;
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
        return parent::insert($request);
    }


    /**
     * 删除
     */
    public function destroy(Request $request):Response
    {
        return parent::delete($request);
    }
    
    /**
     * 执行
     */
    public function run(Request $request):Response
    {
        $id = $request->input('id');
        $res = CrontabLogic::run($id);
        return $this->success($res, '执行成功！');
    }
}