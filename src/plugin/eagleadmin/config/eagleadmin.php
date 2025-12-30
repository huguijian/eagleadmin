<?php
return [
    // 中间件白名单
    'white_list' => [
        '/core/admin/login',
        '/core/admin/get-captcha'
    ],
    //登录是否需要验证码
    'need_captcha' => true,
];