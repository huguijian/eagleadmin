<?php

namespace plugin\eagleadmin\app\admin\controller;

use plugin\eagleadmin\app\BaseController;
use plugin\eagleadmin\app\model\EmsUser;
use plugin\eagleadmin\app\model\EmsUserDepartment;
use support\Request;
use plugin\eagleadmin\app\admin\logic\auth\DepartmentLogic;
class CommonController extends BaseController
{

    /**
     * 搜索用户
     * @param Request $request
     * @return \support\Response
     */
    public function searchUser(Request $request)
    {
        $all = $request->all();
        $list = EmsUser::where('nick_name','like','%'.$all['nick_name'].'%')->get();
        return $this->success($list);
    }


    /**
     * 搜索部门
     * @param Request $request
     * @return \support\Response
     */
    public function searchDepartment(Request $request)
    {
        $params = $request->all();
        $result = DepartmentLogic::getDepartmentList($params);
        return $this->success($result);
    }

    /**
     * 获取用户通过部门
     * @param Request $request
     * @return \support\Response
     */
    public function getUesrByDeparment(Request $request)
    {
        $all = $request->all();
        $userIds = EmsUserDepartment::where('department_id',$all['department_id'])->pluck('user_id')->toArray();
        $list = EmsUser::whereIn('id',$userIds)->get();
        return $this->success($list);
    }


    /**
     * 获取所有用户列表
     * @return \support\Response
     */
    public function getAllUser()
    {
        $list = EmsUser::get();
        return $this->success($list);
    }
}