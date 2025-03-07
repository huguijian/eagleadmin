<?php

namespace plugin\eagleadmin\app\model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $role_id
 */
class EgUserRole extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'eg_user_role';


    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['userId' => 'integer', 'roleId' => 'integer'];


    const CREATED_AT = 'create_time';

    const UPDATED_AT = 'update_time';


    public function roleInfo()
    {
        return $this->hasOne(EgRole::class, 'id','role_id');
    }

}