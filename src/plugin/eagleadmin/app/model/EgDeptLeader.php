<?php

namespace plugin\eagleadmin\app\model;

class EgDeptLeader extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'eg_dept_leader';

    protected $guarded = ['id'];

    const CREATED_AT = 'create_time';

    const UPDATED_AT = 'update_time';

    public function userInfo()
    {
        return $this->hasOne(EgUser::class, 'id', 'user_id');
    }
}