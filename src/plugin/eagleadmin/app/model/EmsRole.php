<?php

namespace plugin\eagleadmin\app\model;
/**
 * @property int $role_id
 * @property string $role_name
 * @property string $remark
 * @property int $create_user_id
 * @property string $create_time
 */
class EmsRole extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ems_role';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['userId' => 'integer', 'roleId' => 'integer'];

    const CREATED_AT = 'create_time';

    const UPDATED_AT = 'update_time';

}