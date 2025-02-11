<?php

namespace plugin\eagleadmin\app\admin\validate;
use plugin\eagleadmin\app\BaseValidate;
class AdminValidate extends BaseValidate
{
    protected $rule =   [
        'username'  => 'require|max:25',
        'password'  => 'require',
    ];

    protected $message  =   [
        'uesrname.require' => '用户名必须',
        'password.require' => '密码必须',


    ];
}