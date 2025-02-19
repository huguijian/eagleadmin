<?php

namespace plugin\eagleadmin\app\model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EgPost extends Base
{
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'eg_post';

    const CREATED_AT = 'create_time';

    const UPDATED_AT = 'update_time';

    const DELETED_AT = 'delete_time';
}
