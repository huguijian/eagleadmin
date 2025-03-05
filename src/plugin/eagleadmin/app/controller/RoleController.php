<?php

namespace plugin\eagleadmin\app\controller;

use plugin\eagleadmin\app\BaseController;
use support\Request;
use support\Response;
use plugin\eagleadmin\app\model\EgRole;
use plugin\eagleadmin\app\logic\RoleLogic;

class RoleController extends BaseController
{

    private $roleLogic=null;
    public function __construct() 
    {
        $this->roleLogic = new RoleLogic();
    }

    /**
     * 角色列表
     * @param Request $request
     * @return Response
     */
    public function select(Request $request) :Response
    {
        $res = $this->roleLogic->select($request);
        return $this->success($res);
    }


    public function update(Request $request)
    {
        $res = $this->roleLogic->update($request);
        return $this->success($res);
    }
    /**
     * 通过角色获取菜单
     * @return Response
     */
    public function getMenuByRole()
    {
        $res = $this->roleLogic->getMenuByRole();
        return $this->success($res);    
    }

    public function getDeptByRole()
    {
        $res = $this->roleLogic->getDeptByRole();
    }

    public function updateMenuPermission($id)
    {
        $this->roleLogic->updateMenuPermission($id);
        return $this->success([], '保存成功！');
    }

    public function updateDataPermission($id)
    {
        $this->roleLogic->updateDataPermission($id);
        return $this->success([], '保存成功！');
    }


    /**
     * 角色回收站
     * @param Request $request
     * @return Response
     */
    public function recycle(Request $request)
    {
        $res = $this->roleLogic->recycle($request);
        return $this->success($res, 'ok');
    }


    /**
     * 恢复角色
     * @param Request $request
     * @return Response
     */
    public function recovery(Request $request)
    {
        $id = $request->input('id');
        EgRole::whereIn('id',$id)->restore();
        return $this->success([],'恢复成功');
    }

    /**
     * 销毁角色
     * @param Request $request
     * @return Response
     */
    public function realDestroy(Request $request)
    {
        $id = $request->input('id');
        EgRole::whereIn('id',$id)->forceDelete();
        return $this->success([]);
    }
}
