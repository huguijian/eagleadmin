<?php

namespace plugin\eagleadmin\app\model;

class EgCrontab extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'eg_crontab';

    const CREATED_AT = 'create_time';

    const UPDATED_AT = 'update_time';

}
