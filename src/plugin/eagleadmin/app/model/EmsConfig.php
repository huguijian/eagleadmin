<?php

namespace plugin\eagleadmin\app\model;

class EmsConfig extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ems_config';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */

    const CREATED_AT = 'create_time';

    const UPDATED_AT = 'update_time';

    protected $guarded = [];
}
