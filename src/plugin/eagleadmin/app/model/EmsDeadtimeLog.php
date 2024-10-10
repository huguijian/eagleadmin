<?php

namespace plugin\eagleadmin\app\model;

/**
 * @property int $menu_id
 * @property int $parent_id
 * @property string $name
 * @property string $url
 * @property string $perms
 * @property int $type
 * @property string $icon
 * @property int $order_num
 */
class EmsDeadtimeLog extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ems_deadtime_log';
}
