<?php

namespace plugin\eagleadmin\app\model;

class EmsUserOrderLog extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ems_user_order_log';

    const STATUS = [
        '未通过' => 0,
        '已通过' => 1,
        '取消预约' => 2,
        '上机' => 3,
        '下机' => 4,
    ];
}
