<?php
namespace plugin\eagleadmin\app\controller\auth;
use plugin\eagleadmin\app\BaseController;
use plugin\eagleadmin\app\logic\auth\DeptLogic;
use plugin\eagleadmin\app\model\EgDept;
use support\Request;
use support\Response;


class DeptController extends BaseController
{
    private $deptLogic;

    public function __construct() 
    {
        $this->deptLogic = new DeptLogic();
    }



    /**
     * 部门列表
     * @param \support\Request $request
     * @return \support\Response
     */
    public function select(Request $request) :Response
    {
        $res = $this->deptLogic->select($request);
        return $this->success($res, 'ok');
    }

    /**
     * 添加部门
     * @param \support\Request $request
     * @return \support\Response
     */
    public function insert(Request $request) :Response
    {
        $res = $this->deptLogic->insert($request);
        return $this->success($res,'添加部门');
    }

    /**
     * 修改部门
     * @param \support\Request $request
     * @return \support\Response
     */
    public function update(Request $request) :Response
    {
        $res = $this->deptLogic->update($request);
        return $this->success($res,'修改部门');
    }

    /**
     * 领导列表
     * @param Request $request
     * @return Response
     */
    public function leaders(Request $request)
    {
        $res = $this->deptLogic->leaders($request);
        return $this->success($res, 'ok');
    }


    /**
     * 删除领导列表
     * @param Request $request
     * @return Response
     */
    public function delLeader(Request $request)
    {
        $msg = '';
        $res = $this->deptLogic->delLeader($request, $msg);
        if (!$res) {
            return $this->error($msg);
        }
        return $this->success([], '删除成功');
    }


    /**
     * 添加部门领导
     * @param Request $request
     * @return Response
     */
    public function addLeader(Request $request)
    {
        $this->deptLogic->addLeader($request);
        return $this->success([]);

    }


    /**
     * 部门回收站
     * @param Request $request
     * @return Response
     */
    public function recycle(Request $request)
    {
        $this->deptLogic->recycle($request);
        return $this->success([]);
    }

    /**
     * 恢复部门
     * @param Request $request
     * @return Response
     */
    public function recovery(Request $request)
    {
        $id = $request->input('id');
        EgDept::whereIn('id',$id)->restore();
        return $this->success([],'恢复成功');
    }

    /**
     * 删除部门
     * @param \support\Request $request
     */
    public function delete(Request $request)
    {
        $this->deptLogic->delete($request);
        return $this->success([],'删除成功');
    }

    /**
     * 销毁删除部门
     * @param Request $request
     * @return Response
     */
    public function realDestroy(Request $request)
    {
        $id = $request->input('id');
        EgDept::whereIn('id',$id)->forceDelete();
        return $this->success([],'删除成功');
    }
}
