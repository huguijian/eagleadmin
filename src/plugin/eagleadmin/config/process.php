<?php
return [
    'tcp_client'  => [
        'handler'  => plugin\eagleadmin\process\Client::class,
        'count'   => 1
    ],
    'task'  => [
        'handler'  => plugin\eagleadmin\process\Task::class,
        'count'   => 1
    ],
];
