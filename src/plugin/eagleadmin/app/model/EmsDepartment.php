<?php

namespace plugin\eagleadmin\app\model;


class EmsDepartment extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ems_department';

    protected $fillable = [
        'name',
        'order_no',
        'remark',
        'parent_id',
        'status',
    ];

    const CREATED_AT = 'create_time';

    const UPDATED_AT = 'update_time';

    public function head()
    {
        return $this->belongsToMany(
            EmsUser::class,
            'ems_department_head',
            'department_id',
            'head_id',
        );
    }
}
