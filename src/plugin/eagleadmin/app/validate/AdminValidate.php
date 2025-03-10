<?php

namespace plugin\eagleadmin\app\validate;
use plugin\eagleadmin\app\BaseValidate;
class AdminValidate extends BaseValidate
{
    protected $rule =   [
        'user_name'  => 'require|max:25',
        'password'  => 'require',
    ];

    protected $message  =   [
        'uesr_name.require' => '用户名必须',
        'password.require' => '密码必须',
    ];
}