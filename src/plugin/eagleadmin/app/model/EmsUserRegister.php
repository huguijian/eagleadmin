<?php

namespace plugin\eagleadmin\app\model;

class EmsUserRegister extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ems_user_register';


    const CREATED_AT = 'create_time';

    const UPDATED_AT = 'update_time';

    protected $guarded = [];

}