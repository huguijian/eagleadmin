<?php

namespace plugin\eagleadmin\app\model;

/**
 * @property int $id
 * @property int $role_id
 * @property int $menu_id
 */
class EgRoleMenu extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'eg_role_menu';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'int', 'roleId' => 'integer', 'menuId' => 'integer'];

    const CREATED_AT = 'create_time';

    const UPDATED_AT = 'update_time';
}