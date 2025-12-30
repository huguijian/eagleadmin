<?php

namespace plugin\eagleadmin\app\controller\auth;

use plugin\eagleadmin\app\logic\auth\MenuLogic;
use plugin\eagleadmin\app\BaseController;
use plugin\eagleadmin\app\model\EgMenu;
use support\Request;
use support\Response;

/**
 * 菜单管理
 */
class MenuController extends BaseController
{

    private $menuLogic;
    public function __construct() 
    {
        $this->menuLogic = new MenuLogic();
    }

    /**
     * 菜单列表
     * @param \support\Request $request
     */
    public function index(Request $request)
    {
        $res = $this->menuLogic->menu($request);
        return $this->success($res, '查询成功！');
    }

    /**
     * 删除菜单
     * @param Request $request
     * @return Response
     */
    public function delete(Request $request):Response
    {
        $ids = $request->input('id');
        $res = EgMenu::whereIn('id', $ids)->delete();
        if ($res) {
            return $this->success([], '删除成功！');
        }
        return $this->error('删除失败！');
    }
    /**
     * 添加菜单
     * @param Request $request
     * @return Response
     * @throws \support\exception\BusinessException
     */
    public function insert(Request $request)
    {
        $res = $this->menuLogic->insert($request);
        return $this->success($res, '添加成功！');
    }

    /**
     * 修改菜单
     * @param \support\Request $request
     * @return \support\Response
     */
    public function update(Request $request):Response
    {
        $res = $this->menuLogic->update($request);
        return $this->success($res,'修改成功！');
    }

    /**
     * 回收站
     * @return Response
     * @throws \support\exception\BusinessException
     */
    public function recycle(Request $request)
    {
        $res = $this->menuLogic->menu($request,true);
        return $this->success($res, '查询成功！');
    }

    /**
     * 恢复
     * @param Request $request
     * @return Response
     */
    public function recovery(Request $request)
    {
        $id = $request->input('id');
        $this->menuLogic->whereIn('id',$id)->restore();
        return $this->success([], '恢复成功！');
    }
    

    /**
     * 销毁
     * @param Request $request
     * @return Response
     */
    public function realDestroy(Request $request)
    {
        $id = $request->input('id');
        $this->menuLogic->whereIn('id',$id)->forceDelete();
        return $this->success([], '删除成功！');
    }

    /**
     * 修改状态
     * @param \support\Request $request
     */
    public function changeStatus(Request $request)
    {
        $res = $this->menuLogic->update($request);
        return $this->success($res,'修改成功！');
    }

}
