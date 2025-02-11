<?php

namespace plugin\eagleadmin\app\admin\controller\auth;

use plugin\eagleadmin\app\admin\logic\auth\RoleLogic;
use plugin\eagleadmin\app\admin\validate\auth\RoleValidate;
use plugin\eagleadmin\app\BaseController;
use plugin\eagleadmin\app\exception\BusinessException;
use plugin\eagleadmin\app\model\EmsRole;
use jwt\JwtInstance;
use support\Request;
use support\Response;

class RoleController extends BaseController
{
    protected $model = null;

    public function __construct()
    {
        $this->model = new EmsRole();
    }

    /**
     * 添加角色
     * @throws BusinessException
     */
    public function insert(Request $request): Response
    {
        $params = (new RoleValidate())->isPost()->validate();
        RoleLogic::addRole($params);
        return $this->success([],"添加成功");

    }


    /**
     * 编辑角色
     * @throws BusinessException
     */
    public function update(Request $request): Response
    {
        if ($request->method()=='POST') {
            $params = (new RoleValidate())->validate('EditRole');
            RoleLogic::editRole($params);
            return $this->success([],'编辑成功');
        }else{
            return $this->info($request);
        }

    }

}