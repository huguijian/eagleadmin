<?php
namespace plugin\eagleadmin\app\logic;

use plugin\eagleadmin\app\logic\ILogic;
use plugin\eagleadmin\app\model\EgLoginLog;
use plugin\eagleadmin\app\model\EgOperateLog;
class SysLogic extends ILogic
{
    private $egOperateLog;
    public function __construct()
    {
        $this->model = new EgLoginLog();
        $this->egOperateLog = new EgOperateLog();
    }

    /**
     * 日志列表 
     * @param mixed $request
     * @return array
     */
    public function loginLog($request)
    {
        $id = $request->input('id');
        if ($id) {
            $this->whereArr[] = ['user_id' => $id];
        }

        $loginTime = $request->input('login_time','');
        $this->whereArr = [
            ['field'=>'user_name','opt'=>'like','val'=>$request->input('user_name')],
            ['field'=>'ip','opt'=>'like','val'=>$request->input('ip')],
            ['field'=>'status','opt'=>'=','val'=>$request->input('status')]
        ];
        if ($loginTime) {
            $this->whereArr[] = ['field'=>'login_time','opt'=>'between','val'=>[$loginTime[0]??'',$loginTime[1]??'']];
        }
        return parent::select($request);
    }

    public function sysLog($request)
    {
        $this->model = $this->egOperateLog; 
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
     * @param mixed $id
     * @return bool
     */
    public function deleteUserLog($id)
    {
        $this->model->whereIn('id',$id)->delete();
        return true;
    }


    /**
     * 删除操作日志
     * @param mixed $id
     * @return bool
     */
    public function deleteOperLog($id)
    {
        $this->egOperateLog->whereIn('id',$id)->delete();
        return true;
    }
}