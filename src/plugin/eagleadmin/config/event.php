<?php
return [
    'user.login' => [
        [plugin\eagleadmin\app\event\SystemUser::class, 'login'],
    ],
    'user.operateLog' => [
        [plugin\eagleadmin\app\event\SystemUser::class, 'operateLog'],
    ]
];