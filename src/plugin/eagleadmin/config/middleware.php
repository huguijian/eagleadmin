<?php

return [
    // 全局中间件
    '' => [
        // ... 这里省略其它中间件
        plugin\eagleadmin\app\middleware\LogMiddleware::class,
        plugin\eagleadmin\app\middleware\CorsMiddleware::class,
    ],
    //应用中间件(应用中间件仅在多应用模式下有效)
    'admin' => [
//        plugin\eagleadmin\api\Middleware::class,
    ],
];
