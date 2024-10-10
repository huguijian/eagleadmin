<?php

namespace plugin\eagleadmin\app\model;

use app\model\SsyDevice;

class EmsCostRule extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ems_cost_rule';


    const CREATED_AT = 'create_time';

    const UPDATED_AT = 'update_time';

}