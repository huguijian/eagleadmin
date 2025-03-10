<?php

namespace plugin\eagleadmin\app\admin\validate\auth;

use plugin\eagleadmin\app\BaseValidate;
use plugin\eagleadmin\app\model\EgUser;

class UserValidate extends BaseValidate
{
    protected $rule = [
        'user_name' => 'require|checkUserName',
        'nick_name' => 'require',
        'role_ids'  => 'require',
        'password'  => 'require',
        'department_ids' => 'checkDepartmentIds',
    ];

    protected $message = [
        'user_name.require' => '用户名必须',
        'nick_name.require' => '昵称必须',
        'role_ids.require'  => '角色必须',
        'password.require'  => '密码必须',
        'id' => 'ID必须',
        'password_old' => '旧密码必须',
        'password_new' => '新密码必须'
    ];

    protected function checkDepartmentIds($value, $rule, $data = [], $field = '')
    {
        $depIds = $data['department_ids'] ?? [];
        if ($depIds && !is_array($depIds)) {
            return 'department_ids必须是数组！';
        }
        return true;
    }

    public function checkUserName($value,$rule,$data=[]): bool|string
    {
        $userInfo = EgUser::where("user_name",$value)->exists();
        if (false!==$userInfo) {
            return '用户名已经存在';
        }
        return true;
    }

    public function sceneEditUser(): UserValidate
    {
        return $this->only(['id','nick_name','role_ids','department_ids'])
            ->append('id','require');
    }

    public function sceneDelUser(): UserValidate
    {
        return $this->only(['id'])
            ->append('id','require');
    }

    public function sceneChangePw(): UserValidate
    {
        return $this->only(['password_new','password_old'])
            ->append('password_new',['require'])
            ->append('password_old',['require']);
    }
}
