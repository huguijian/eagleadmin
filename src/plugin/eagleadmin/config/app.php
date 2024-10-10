<?php

use support\Request;

return [
    'debug' => env('APP_DEBUG'),
    'controller_suffix' => 'Controller',
    'controller_reuse' => false,
    'version' => '1.0.0',
    'jsonnl_address' => env('WECHAT_TCP_SERVER_HOST', '127.0.0.1').':'.env('WECHAT_TCP_SERVER_PORT', '7474'),
    'system_id' => env('SYSTEM_ID'),
    'ems_token_expire' => 7200,
];
