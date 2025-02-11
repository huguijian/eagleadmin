<?php

namespace plugin\eagleadmin\app\model;
use Illuminate\Database\Eloquent\SoftDeletes;
class EgLoginLog extends Base
{
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'eg_login_log';

    /**
     * 受保护的属性(字段)
     * @var string[]
     */
    protected $guarded = ['id'];

    const CREATED_AT = 'create_time';

    const UPDATED_AT = 'update_time';

    const DELETED_AT = 'delete_time';

    public function userInfo()
    {
        return $this->hasOne(EgUser::class,'id','user_id');
    }
}
