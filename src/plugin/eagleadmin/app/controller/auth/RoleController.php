<?php

namespace plugin\eagleadmin\app\controller\auth;

use plugin\eagleadmin\app\BaseController;
use support\Request;
use support\Response;
use plugin\eagleadmin\app\model\EgRole;
use plugin\eagleadmin\app\logic\auth\RoleLogic;

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


    /**
     * 更新角色
     * @param \support\Request $request
     */
    public function update(Request $request)
    {
        $res = $this->roleLogic->update($request);
        return $this->success($res);
    }

    /**
     * 添加角色
     * @param \support\Request $request
     * @return \support\Response
     */
    public function insert(Request $request):Response
    {
        $res = $this->roleLogic->insert($request);
        return $this->success($res,'添加成功');
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

    /**
     * 获取角色部门
     * @return void
     */
    public function getDeptByRole()
    {
        $res = $this->roleLogic->getDeptByRole();
        return $this->success($res);
    }

    /**
     * 菜单权限
     */
    public function updateMenuPermission(Request $request)
    {
        $this->roleLogic->updateMenuPermission($request);
        return $this->success([], '保存成功！');
    }

    /**
     * 数据权限
     * @param mixed $id
     */
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

    /*
     * 删除角色
     * @param Request $request
     * @return Response
     */
    public function delete(Request $request)
    {
        $res = $this->roleLogic->delete($request);
        return $this->success($res,'删除成功');
    }
}
