<?php

namespace plugin\eagleadmin\app\admin\validate\auth;

use plugin\eagleadmin\app\BaseValidate;

class  DeptValidate extends BaseValidate
{
    protected $rule = [
        'name' => 'require',
        'id' => 'require',
    ];

    protected $message = [
        'name.require' => '部门名称必须',
        'id.require' => 'id必传!',
    ];

    protected $scene = [
        'update' => [
            'id',
        ],
        'insert' => [
            'name',
        ],
    ];
}
