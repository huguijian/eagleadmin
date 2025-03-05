<?php

namespace plugin\eagleadmin\app\admin\validate\auth;

use plugin\eagleadmin\app\BaseValidate;

class MenuValidate extends BaseValidate
{
    protected $rule = [
        'name' => 'require',
    ];

    protected $message = [
        'name.require'  => '菜单名称必须',
        'id.require' => '菜单ID不能为空',
        'id.number' => '菜单ID只能为数字',
        'pid.require' => '目标菜单ID不能为空',
        'pid.number' => '目标菜单ID只能为数字',
    ];

    public function sceneSortTable()
    {
        return $this->only(['id','target_id','weigh'])
            ->append('id','require|number')
            ->append('target_id','require|number');
    }
}