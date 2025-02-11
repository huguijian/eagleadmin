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
class EgMenu extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'eg_menu';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */

    const CREATED_AT = 'create_time';

    const UPDATED_AT = 'update_time';

}