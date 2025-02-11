<?php

namespace plugin\eagleadmin\app\model;

class EgOperateLog extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'eg_operate_log';

    protected $guarded = ['id'];

    const CREATED_AT = 'create_time';

    const UPDATED_AT = 'update_time';

}
