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

    public function insert(Request $request): Response
    {
        $params = $request->all();
        $params['avatar'] = $params['avatar'] ?? 'aab';
        $this->params = $params;
        return parent::insert($request);
    }

    public function update(Request $request): Response
    {
        return parent::update($request);
    }

    public function select(Request $request): Response
    {
        $search = $request->input('search');
        var_dump($search);
        var_dump($search[0]['field']);
        return parent::select($request);
    }
}
