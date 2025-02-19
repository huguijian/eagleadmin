<?php

namespace plugin\eagleadmin\app\admin\controller;
use plugin\eagleadmin\app\model\EgOperateLog;
use support\Request;
use support\Response;
use plugin\eagleadmin\app\BaseController;
use plugin\eagleadmin\app\model\EgLoginLog;

class SysController extends BaseController
{
    protected $noNeedAuth = ['loginLog', 'sysLog'];


    /**
     * 登录日志
     * @param Request $request
     * @return Response
     */
    public function loginLog(Request $request)
    {
        $id = $request->input('id');
        if ($id) {
            $this->whereArr[] = ['user_id' => $id];
        }

        $loginTime = $request->input('login_time','');
        $this->whereArr = [
            ['field'=>'user_name','opt'=>'like','val'=>$request->input('user_name')],
            ['field'=>'ip','opt'=>'like','val'=>$request->input('ip')],
            ['field'=>'status','opt'=>'=','val'=>$request->input('status')],
        ];

        if ($loginTime) {
            $this->whereArr[] = ['field'=>'login_time','opt'=>'between','val'=>[$loginTime[0]??'',$loginTime[1]??'']];
        }
        $this->model = new EgLoginLog();
        return parent::select($request);
    }


    /**
     * 操作日志
     * @param Request $request
     * @return Response
     */
    public function sysLog(Request $request)
    {
        $this->model = new EgOperateLog();
        $createTime = $request->input('create_time','');
        $this->whereArr = [
            ['field'=>'user_name','opt'=>'like','val'=>$request->input('user_name')],
            ['field'=>'ip','opt'=>'like','val'=>$request->input('ip')],
            ['field'=>'router','opt'=>'like','val'=>$request->input('router')],
        ];

        if ($createTime) {
            $this->whereArr[] = ['field'=>'create_time','opt'=>'between','val'=>[$createTime[0]??'',$createTime[1]??'']];
        }

        return parent::select($request);
    }


    /**
     * 删除登录日志
     * @param Request $request
     * @return Response
     */
    public function deleteLoginLog(Request $request)
    {
        $id = $request->input('id');
        EgLoginLog::whereIn('id',$id)->delete();
        return $this->success([]);
    }

    /**
     * 删除操作日志
     * @param Request $request
     * @return Response
     */
    public function deleteOperLog(Request $request)
    {
        $id = $request->input('id');
        EgOperateLog::whereIn('id',$id)->delete();
        return $this->success([]);
    }
}
