<?php

namespace plugin\eagleadmin\app\validate\auth;

use plugin\eagleadmin\app\BaseValidate;
use plugin\eagleadmin\app\model\EgRole;

class RoleValidate extends BaseValidate
{
    protected $rule = [
        'name' => 'require|checkRoleName',
        'rules' => 'require',
        'status' => 'require',

    ];

    protected $message = [
        'name.require'  => '角色名必须',
        'rules.require' => '菜单必须',
        'rules.array'   => '菜单必须为数组',
        'status.require' => '状态必须',
        'id.require' => 'ID必须'
    ];

    public function checkRoleName($value,$rule,$data=[]): bool|string
    {
        if (!empty($data['id'])) {
            $exists = EgRole::where("name",$value)->where("id","<>",$data['id'])->exists();
        }else {
            $exists = EgRole::where("name", $value)->exists();
        }

        if (false!==$exists) {
            return "角色名重复";
        }
        return true;
    }

    public function sceneEditRole(): RoleValidate
    {
        return $this->only(['id','name','status'])
            ->append('id','require');
    }
}