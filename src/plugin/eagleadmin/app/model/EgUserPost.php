<?php

namespace plugin\eagleadmin\app\model;


class EgUserPost extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'eg_user_post';

    const CREATED_AT = 'create_time';

    const UPDATED_AT = 'update_time';
}
