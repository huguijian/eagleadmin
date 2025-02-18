<?php

namespace plugin\eagleadmin\app\model;


class EgDept extends Base
{
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


}
