<?php

namespace plugin\eagleadmin\app\model;


class EgPost extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'eg_post';

    const CREATED_AT = 'create_time';

    const UPDATED_AT = 'update_time';
}
