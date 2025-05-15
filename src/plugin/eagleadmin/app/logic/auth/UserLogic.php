<?php

namespace plugin\eagleadmin\app\logic\auth;
use plugin\eagleadmin\app\exception\BusinessException;
use plugin\eagleadmin\app\logic\ILogic;
use plugin\eagleadmin\app\model\EgMenu;
use plugin\eagleadmin\app\model\EgUser;
use plugin\eagleadmin\app\model\EgUserPost;
use plugin\eagleadmin\app\model\EgUserRole;
use plugin\eagleadmin\utils\Helper;
use support\Db;
use support\Request;

class UserLogic extends ILogic
{

    public function __construct()
    {
        $this->model = new EgUser();
    }
    /**
     * 获取全部菜单
     */
    public function getAllMenus(): array
    {
        $allMenus = EgMenu::where(['type' => ['M','I','L']])
            ->orderBy('sort', 'asc')
            ->get()
            ->all();
        return Helper::makeArcoMenus($allMenus);
    }

    /**
     * 获取指定用户权限标识列表
     * @param mixed $user
     */
    public function getCodes($user)
    {
        // 获取用户的角色列表
        $roles = optional($user)->roles;

        // 获取用户的角色对应的菜单列表
        $menus = $roles ? $roles->pluck('menus') : null;
        // 打散合并
        $menus = $menus ? $menus->collapse() : null;

        // 菜单的路径列表
        $codes = $menus ? $menus->pluck('code') : null;
        $codes = $codes ? $codes->unique()->values()->toArray() : [];
        return $codes;
    }

    /**
     * 获取用户的角色名列表
     * @param mixed $user
     */
    public function getRoles($user)
    {
        // 获取用户的角色列表
        $roles = optional($user)->roles;

        return $roles ? $roles->pluck('code')->toArray() : [];
    }


    /**
     * 获取用户菜单
     * @param mixed $user
     * @return array
     */
    public function getMenus($user)
    {
        // 获取用户的角色列表
        $roles = optional($user)->roles ?? null;

        // 获取用户的角色对应的菜单列表
        $menus = $roles ? $roles->pluck('menus') : null;
        $menus = $menus ? $menus->toArray() : [];
        $menus = array_merge(...$menus);
        $menus = $menus ? collect($menus)->unique()
            ->values()
            ->toArray() : [];
        return Helper::makeArcoMenus($menus);
    }


    /**
     * 修改密码
     * @param $params
     * @param $code
     * @param $msg
     * @return bool
     */
    public function modifyPassword($params,&$code,&$msg)
    {
        $userInfo = EgUser::where('id',admin_id())->first()->makeVisible('password');
        $userInfo = collect($userInfo)->toArray();
        if (!password_verify($params['oldPassword'], $userInfo["password"])) {
            $code = -1;
            $msg  = "密码错误!";
            return false;
        }

        if ($params['newPassword']!=$params['newPassword_confirmation']) {
            $code = -1;
            $msg  = "两次密码不一致!";
            return false;
        }

        $password = password_hash($params['newPassword'], PASSWORD_BCRYPT, ["cost" => 12]);
        EgUser::where('id', admin_id())->update(['password' => $password]);
        return true;
    }

    /**
     * 获取指定用户ID信息
     * @param $params
     * @param $code
     * @param $msg
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Collection[]|EgUser[]
     */
    public function getUserInfoByIds($params,&$code,&$msg)
    {
        $res = EgUser::whereIn('id',$params['id'])->get();
        return $res;
    }


    /**
     * 用户回收站
     * @param \support\Request $request
     * @return array
     */
    public  function recycle(Request $request)
    {
        [$where, $pageSize, $order] = $this->selectInput($request);

        $registerTime = $request->get('create_time');
        array_push($where,
            ['field'=>'user_name','opt'=>'like','val'=>$request->get('user_name')],
            ['field'=>'phone','opt'=>'like','val'=>$request->get('phone')],
            ['field'=>'email','opt'=>'like','val'=>$request->get('email')],
            ['field'=>'dept_id','opt'=>'=','val'=>$request->get('dept_id')]
        );

        $roleId = $request->get('role_id','');
        if ($roleId) {
            $userIds = EgUserRole::where('role_id',$roleId)->pluck('user_id');
            $userIds = collect($userIds)->toArray();
            $where[] = ['field'=>'id','opt'=>'in','val'=>$userIds];
        }

        $postId = $request->get('post_id','');
        if ($postId) {
            $userIds = EgUserPost::where('post_id',$postId)->pluck('user_id');
            $userIds = collect($userIds)->toArray();
            $where[] = ['field'=>'id','opt'=>'in','val'=>$userIds];
        }

        if ($registerTime) {
            $where[] = ['field' => 'create_time', 'opt' => 'between', 'val' => [$registerTime[0], $registerTime[1]]];
        }
        $model = $this->selectMap($where,$order);
        $model->onlyTrashed();
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
        return $res;
    }


    /**
     * 获取登录用户信息
     * @return array
     */
    public function loginInfo()
    {
        $user = EgUser::where('id','=', admin_id())->first();
        $info['user'] = $user;
        if (isset($user) && $user['id'] === 1) {
            $info['codes'] = ['*'];
            $info['roles'] = ['superAdmin'];
            $info['routers'] = $this->getAllMenus();
        } else {
            // 菜单路径列表
            $info['codes'] = $this->getCodes($user);
            // 角色名称列表
            $info['roles'] = $this->getRoles($user);
            $info['routers'] = $this->getMenus($user);
        }
        return $info;
    }

    /**
     * 添加用户
     * @return bool
     */
    public function addUser($params,&$msg): bool
    {
        $params['avatar'] = $params['avatar'] ?? '';
        $roleIds = $params['role_ids'];
        $postIds = $params['post_ids'];


        $userInfo = EgUser::where(['user_name'=>$params['user_name']])->first();
        if ($userInfo) {
            $msg = '用户名已存在！';
            return false;
        }

        $password = $params['password'] ?? '';
        if (!$password) {
            $msg = '密码必填！';
            return false;
        }
        $inputData = $this->inputFilter((array)$params);
        try {
            Db::beginTransaction();
            $inputData['password'] = password_hash($inputData['password'], PASSWORD_BCRYPT, ["cost" => 12]);
            $userId = EgUser::insertGetId($inputData);

            $refData = [];
            foreach($roleIds as $roleId) {
                $refData[]  = [
                    'user_id' => $userId,
                    'role_id' => $roleId,
                ];
            }
            EgUserRole::insert($refData);

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
        return true;
    }


    /**
     * 用户信息
     * @param mixed $request
     * @return array{row: mixed}
     */
    public function userInfo($request)
    {
        $this->callBack = function($item) {
            $role = $item['roles'] ?? [];
            $item['role_ids'] = $role ? $role->pluck('id') : [];
            $posts = $item['posts'] ?? [];
            $item['post_ids'] = $posts ? $posts->pluck('id') : [];
            return $item;
        };
        return $this->info($request);
    }

    /**
     * 修改用户
     * @param mixed $request
     * @throws \plugin\eagleadmin\app\exception\BusinessException
     * @return array{row: bool|mixed|bool}
     */
    public function update($request)    
    {
        if ($request->method() == "POST") {
            $roleIds = $request->input('role_ids');
            $postIds = $request->input('post_ids');
            $id = $request->input('id');
            if (!$id) {
                throw new BusinessException('用户ID不能为空');
            }
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
           return true;
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

    /**
     * 用户列表
     * @param \support\Request $request
     * @return array
     */
    public function select(Request $request)
    {
        [$where, $pageSize, $order] = $this->selectInput($request);

        $registerTime = $request->get('create_time');
        array_push($where,
            ['field'=>'user_name','opt'=>'like','val'=>$request->get('user_name')],
            ['field'=>'phone','opt'=>'like','val'=>$request->get('phone')],
            ['field'=>'email','opt'=>'like','val'=>$request->get('email')],
            ['field'=>'dept_id','opt'=>'=','val'=>$request->get('dept_id')]
        );

        $roleId = $request->get('role_id','');
        if ($roleId) {
            $userIds = EgUserRole::where('role_id',$roleId)->pluck('user_id');
            $userIds = collect($userIds)->toArray();
            $where[] = ['field'=>'id','opt'=>'in','val'=>$userIds];
        }

        $postId = $request->get('post_id','');
        if ($postId) {
            $userIds = EgUserPost::where('post_id',$postId)->pluck('user_id');
            $userIds = collect($userIds)->toArray();
            $where[] = ['field'=>'id','opt'=>'in','val'=>$userIds];
        }

        if ($registerTime) {
            $where[] = ['field' => 'create_time', 'opt' => 'between', 'val' => [$registerTime[0], $registerTime[1]]];
        }
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
            $list = call_user_func($this->callBack, $list);
        }
        $res['items'] = $list;
        return $res;
    }
}
