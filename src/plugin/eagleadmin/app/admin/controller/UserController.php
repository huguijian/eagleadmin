<?php

namespace plugin\eagleadmin\app\admin\controller;

use plugin\eagleadmin\app\BaseController;
use support\Request;
use support\Db;
use support\Response;
use plugin\eagleadmin\app\admin\logic\UserLogic;
use plugin\eagleadmin\app\UploadValidator;
use plugin\eagleadmin\app\service\CommonService;
use plugin\eagleadmin\app\model\EgUser;
use plugin\eagleadmin\app\model\EgUserRole;

class UserController extends BaseController
{
    protected $model;

    protected $noNeedAuth = ['loginInfo'];

    public function __construct() {
        $this->model = new EgUser();
    }

    /**
     * 获取登录用户信息
     */
    public function loginInfo(): Response
    {
        $logic = new UserLogic();
        $user = getUserInfo();
        $info['user'] = optional($user)->toArray();
        if (isset($user) && $user['id'] === 1) {
            $info['codes'] = ['*'];
            $info['roles'] = ['superAdmin'];
            $info['routers'] = $logic->getAllMenus();
        } else {
            // 菜单路径列表
            $info['codes'] = $logic->getCodes($user);
            // 角色名称列表
            $info['roles'] = $logic->getRoles($user);
            // 菜单路由列表
            $info['routers'] = $logic->getMenus($user);
        }
        return $this->success($info);
    }

    public function insert(Request $request): Response
    {
        $params = $request->all();
        $params['avatar'] = $params['avatar'] ?? '';
        $password = $params['password'] ?? '';
        if (!$password) {
            return $this->error('密码必填！');
        }
        $inputData = $this->inputFilter($params);
        try {
            Db::beginTransaction();
            $inputData['password'] = password_hash($inputData['password'], PASSWORD_BCRYPT, ["cost" => 12]);
            $userId = EgUser::insertGetId($inputData);
            $roleIds = $request->input('role_ids');

            $refData = [];
            foreach($roleIds as $roleId) {
                $refData[]  = [
                    'user_id' => $userId,
                    'role_id' => $roleId,
                ];
            }
            EgUserRole::insert($refData);
            Db::commit();
        } catch(\Exception $e) {
            Db::rollBack();
            throw $e;
        }
        return $this->success([], '添加成功！');
    }

    public function update(Request $request): Response
    {
        if ($request->method() == "POST") {
            $roleIds = $request->input('role_ids');
            $id = $request->input('id');
            $password = $request->input('password');
            $params = $request->all();
            $inputData = $this->inputFilter($params);
            try {
                Db::beginTransaction();
                $refData = [];
                EgUserRole::where('user_id', $id)->delete();
                foreach($roleIds as $roleId) {
                    $refData[]  = [
                        'user_id' => $id,
                        'role_id' => $roleId,
                    ];
                }
                EgUserRole::insert($refData);
                if ($password) {
                    $inputData['password'] = password_hash($password, PASSWORD_BCRYPT, ["cost" => 12]);
                }
                EgUser::where('id', $id)->update($inputData);
                Db::commit();
            } catch(\Exception $e) {
                Db::rollBack();
                throw $e;
            }
            return $this->success([], '更新成功！');
        } else {
            $this->callBack = function($item) {
                $role = $item['roles'] ?? [];
                $item['role_ids'] = $role ? $role->pluck('id') : [];
                return $item;
            };
            return parent::update($request);
        }
    }

    public function select(Request $request): Response
    {
        $search = $request->input('search');
        return parent::select($request);
    }

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

    public function initPassword(Request $request): Response
    {
        $password = 'ssy123';
        $id = $request->input('id');
        $password = password_hash($password, PASSWORD_BCRYPT, ["cost" => 12]);
        EgUser::where('id', $id)->update(['password' => $password]);
        return $this->success([], '重置密码成功！');
    }
}
