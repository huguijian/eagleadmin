<?php

namespace plugin\eagleadmin\app\model;

class EgAttachment extends Base
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'eg_attachment';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    protected $guarded = ['id','create_time','update_time'];

    const TYPE = [
        'common' => 0,
        'material' => 1,
    ];

}
