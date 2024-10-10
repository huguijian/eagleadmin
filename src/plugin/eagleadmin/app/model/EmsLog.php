<?php

namespace plugin\eagleadmin\app\model;

class EmsLog extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ems_log';


    const CREATED_AT = 'create_time';

    const UPDATED_AT = 'update_time';

    public function userInfo()
    {
        return $this->hasOne(EmsUser::class,'id','user_id');
    }

}