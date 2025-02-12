<?php

namespace plugin\eagleadmin\app\admin\controller;

use plugin\eagleadmin\app\BaseController;
use support\Request;
use support\Db;
use support\Response;
use plugin\eagleadmin\app\admin\logic\UserLogic;
use plugin\eagleadmin\app\model\EgUser;
use plugin\eagleadmin\app\model\EgUserPost;
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
     * @return Response
     */
    public function loginInfo(): Response
    {
        $logic = new UserLogic();
        $user = getUserInfo();
        $info['user'] = $user;
        if (isset($user) && $user['id'] === 1) {
            $info['codes'] = ['*'];
            $info['roles'] = ['superAdmin'];
            $info['routers'] = $logic->getAllMenus();
        } else {
            // 菜单路径列表
            $info['codes'] = $logic->getCodes($user);
            // 角色名称列表
            $info['roles'] = $logic->getRoles($user);
            $info['routers'] = $logic->getMenus($user);
        }
        return $this->success($info);
    }

    public function insert(Request $request): Response
    {
        $params = $request->all();
        $params['avatar'] = $params['avatar'] ?? '';

        $userInfo = EgUser::where(['user_name'=>$params['user_name']])->first();
        if ($userInfo) {
            return $this->error('用户名已存在！');
        }

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

            $postIds = $request->input('post_ids');
            $refData = [];
            foreach($postIds as $postId) {
                $refData[]  = [
                    'user_id' => $userId,
                    'post_id' => $postId,
                ];
            }
            EgUserPost::insert($refData);

            Db::commit();
        } catch(\Exception $e) {
            Db::rollBack();
            throw $e;
        }
        return $this->success([], '添加成功！');
    }

    public function info(Request $request,$model=null): Response
    {
        $this->callBack = function($item) {
            $role = $item['roles'] ?? [];
            $item['role_ids'] = $role ? $role->pluck('id') : [];
            $posts = $item['posts'] ?? [];
            $item['post_ids'] = $posts ? $posts->pluck('id') : [];
            return $item;
        };
        return parent::info($request);
    }

    public function update(Request $request): Response
    {
        if ($request->method() == "POST") {
            $roleIds = $request->input('role_ids');
            $postIds = $request->input('post_ids');
            $id = $request->input('id');
            $password = $request->input('password');
            $params = $request->all();
            $inputData = $this->inputFilter($params);
            try {
                Db::beginTransaction();
                $refData = [];
                if ($roleIds) {
                  EgUserRole::where('user_id', $id)->delete();
                  foreach($roleIds as $roleId) {
                      $refData[]  = [
                          'user_id' => $id,
                          'role_id' => $roleId,
                      ];
                  }
                  EgUserRole::insert($refData);
                }

                if ($postIds) {
                  EgUserPost::where('user_id', $id)->delete();
                  $refData = [];
                  foreach($postIds as $postId) {
                      $refData[]  = [
                          'user_id' => $id,
                          'post_id' => $postId,
                      ];
                  }
                  EgUserPost::insert($refData);
                }

                if ($password) {
                    $inputData['password'] = password_hash($password, PASSWORD_BCRYPT, ["cost" => 12]);
                } else {
                    unset($inputData['password']);
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
                $posts = $item['posts'] ?? [];
                $item['post_ids'] = $posts ? $posts->pluck('id') : [];
                return $item;
            };
            return parent::update($request);
        }
    }

    public function select(Request $request): Response
    {
        [$where, $pageSize, $order] = $this->selectInput($request);

        $registerTime = $request->get('create_time');
        array_push($where,
            ['field'=>'user_name','opt'=>'like','val'=>$request->get('user_name')],
            ['field'=>'phone','opt'=>'like','val'=>$request->get('phone')],
            ['field'=>'email','opt'=>'like','val'=>$request->get('email')]
        );

        if ($registerTime) {
            $where[] = ['field' => 'create_time', 'opt' => 'between', 'val' => [$registerTime[0], $registerTime[1]]];
        }


        $order = $this->orderBy ?? 'id,desc';
        $model = $this->selectMap($where,$order);
        if ($this->pageSize == -1) { // 值为-1表示不分页
            $list = $model->get() ?? [];
        } else {
            $pageSize = $this->pageSize > 0 ? $this->pageSize : $pageSize;
            $paginator = $model->paginate($pageSize);
            $list = $paginator->items() ?? [];
            $res['total'] = $paginator->total();
        }
        if ($this->callBack && is_callable($this->callBack)) {
            $list = call_user_func($this->callBack, $list) ?? [];
        }
        $res['items'] = $list;
        return $this->success($res, 'ok');
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


    /**
     * 修改密码
     * @param Request $request
     * @return Response
     */
    public function modifyPassword(Request $request)
    {
        $params = $request->all();
        $res = UserLogic::modifyPassword($params,$code,$msg);
        if ($res===false) {
            return $this->error($msg);
        }

        return $this->success([],'密码修改成功！');
    }
}
