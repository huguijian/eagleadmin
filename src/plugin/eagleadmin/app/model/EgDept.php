<?php

namespace plugin\eagleadmin\app\model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EgDept extends Base
{
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'eg_dept';

    protected $fillable = [
        'name',
        'sort',
        'remark',
        'parent_id',
        'status',
    ];

    const CREATED_AT = 'create_time';

    const UPDATED_AT = 'update_time';

    const DELETED_AT = 'delete_time';
}
