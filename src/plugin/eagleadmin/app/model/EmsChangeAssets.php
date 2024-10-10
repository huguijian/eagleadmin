<?php

namespace plugin\eagleadmin\app\model;

class EmsChangeAssets extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ems_change_assets';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */

    const CREATED_AT = 'create_time';

    const UPDATED_AT = 'update_time';

    protected $guarded = [];

    public function deviceInfo()
    {
        return $this->hasOne(EmsDevice::class,'id', 'device_id');
    }
}
