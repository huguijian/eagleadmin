<?php

namespace plugin\eagleadmin\app\controller\auth;
use plugin\eagleadmin\app\BaseController;
use support\Request;
use support\Response;
use plugin\eagleadmin\app\logic\auth\UserLogic;
use plugin\eagleadmin\app\model\EgUser;


class UserController extends BaseController
{

    protected $noNeedAuth = ['loginInfo'];

    private $userLogic;
    public function __construct()
    {
        $this->userLogic = new UserLogic();
    }

    /**
     * 添加用户
     * @param Request $request
     * @return Response
     * @throws \support\exception\BusinessException
     */
    public function insert(Request $request): Response
    {
        $params = $request->all();
        $msg = '';
        $res = $this->userLogic->addUser($params, $msg);
        if ($res==false) {
            return $this->error($msg);
        }

        return $this->success([], '添加成功！');
    }


    /**
     * 删除用户
     * @param \support\Request $request
     * @return \support\Response
     */
    public function delete(Request $request): Response
    {
        $res = $this->userLogic->delete($request);
        return $this->success([],'删除成功');
        
    }

    /**
     * 获取用户信息
     * @param \support\Request $request
     * @param mixed $model
     * @return \support\Response
     */
    public function info(Request $request,$model=null): Response
    {
        $res = $this->userLogic->userInfo($request);
        return $this->success($res);
    }


    /**
     * 更新用户
     * @param Request $request
     * @return Response
     * @throws \support\exception\BusinessException
     */
    public function update(Request $request): Response
    {
        $res = $this->userLogic->update($request);
        return $this->success($res, '更新成功！');
    }


    /**
     * 用户列表
     * @param Request $request
     * @return Response
     */
    public function select(Request $request): Response
    {
        $res = $this->userLogic->select($request);
        return $this->success($res, 'ok');
    }


    /**
     * 改变状态
     * @param Request $request
     * @return Response
     */
    public function changeStatus(Request $request): Response
    {
        $params = $request->all();
        $id = $params['id'];
        $status = $params['status'];
        $res = EgUser::where('id', $id)->update(['status' => $status]);
        if ($res) {
            return $this->success([], '更新成功！');
        }
        return $this->error('更新失败');
    }


    /**
     * 重置密码
     * @param Request $request
     * @return Response
     */
    public function initPassword(Request $request): Response
    {
        $password = 'ssy123';
        $id = $request->input('id');
        $password = password_hash($password, PASSWORD_BCRYPT, ["cost" => 12]);
        EgUser::where('id', $id)->update(['password' => $password]);
        return $this->success([], '重置密码成功！');
    }


    /**
     * 修改密码
     * @param Request $request
     * @return Response
     */
    public function modifyPassword(Request $request)
    {
        $params = $request->all();
        $code = 0;
        $msg = '';
        $res = $this->userLogic->modifyPassword($params, $code, $msg);
        if ($res===false) {
            return $this->error($msg);
        }

        return $this->success([],'密码修改成功！');
    }


    /**
     * 获取指定用户ID信息
     * @param Request $request
     * @return Response
     */
    public function getUserInfoByIds(Request $request)
    {
        $params = $request->all();
        $code = 0;
        $msg = '';
        $res = $this->userLogic->getUserInfoByIds($params, $code, $msg);
        return $this->success($res);
    }


    /**
     * 用户回收站
     * @param Request $request
     * @return Response|void
     */
    public function recycle(Request $request)
    {
       $res = $this->userLogic->recycle($request); 
       return $this->success($res);
    }


    /**
     * 恢复用户
     * @param Request $request
     * @return Response
     */
    public function recovery(Request $request)
    {
        $id = $request->input('id');
        EgUser::whereIn('id',$id)->onlyTrashed()->restore();
        return $this->success([]);
    }


    /**
     * 销毁用户
     * @param Request $request
     * @return Response
     */
    public function realDestroy(Request $request)
    {

        $id = $request->input('id');
        EgUser::whereIn('id',$id)->onlyTrashed()->forceDelete();
        return $this->success([]);
    }


    /**
     * 获取登录用户信息
     * @param Request $request
     * @return Response
     */
    public function loginInfo(Request $request)
    {
        $res = $this->userLogic->loginInfo();
        return $this->success($res);
    }
}
