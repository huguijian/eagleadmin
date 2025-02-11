<?php

namespace plugin\eagleadmin\app\model;

class EgConfig extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'eg_config';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */

    const CREATED_AT = 'create_time';

    const UPDATED_AT = 'update_time';

    protected $guarded = [];
}
