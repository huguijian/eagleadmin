<?php

namespace plugin\eagleadmin\app\model;

class EgSystemConfig extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'eg_system_config';

    public $timestamps = false;

    public function groupInfo()
    {
        return $this->hasOne(EgSystemConfigGroup::class, 'id', 'group_id');
    }
}
