<?php

namespace plugin\eagleadmin\app\model;

class EgCrontabLog extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'eg_crontab_log';

    const CREATED_AT = 'create_time';

    const UPDATED_AT = 'update_time';

}
