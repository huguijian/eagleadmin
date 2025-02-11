<?php

namespace plugin\eagleadmin\app\model;

class EgSystemConfigGroup extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'eg_system_config_group';

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
}
