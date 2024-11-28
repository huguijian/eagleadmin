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

class UserController extends BaseController
{
    protected $model;

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

    public function update(Request $request): Response
    {
        if ($request->method() == "POST") {
            $id = $request->input('id', getUid());
            $params = $request->all();
            $this->model = new EgUser();
            $params = $this->inputFilter($params);
            $res = EgUser::where('id', $id)->update($params);
            if ($res) {
                return $this->success([], '更新成功!');
            }
            return $this->error('更新失败!');
        }
    }

    public function select(Request $request): Response
    {
        $search = $request->input('search');
        var_dump($search);
        var_dump($search[0]['field']);
        $res = $this->selectData($request);
        return $this->success($res, 'ok');
    }
}
