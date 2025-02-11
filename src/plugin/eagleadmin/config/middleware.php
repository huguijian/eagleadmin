<?php

return [
    // 全局中间件
    '' => [
        // ... 这里省略其它中间件
        plugin\eagleadmin\app\middleware\CorsMiddleware::class,
        plugin\eagleadmin\api\Middleware::class,
    ],
];
