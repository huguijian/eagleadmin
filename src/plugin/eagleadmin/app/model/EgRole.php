<?php

namespace plugin\eagleadmin\app\model;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * @property int $role_id
 * @property string $role_name
 * @property string $remark
 * @property int $create_user_id
 * @property string $create_time
 */
class EgRole extends Base
{
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'eg_role';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['userId' => 'integer', 'roleId' => 'integer'];

    const DELETED_AT = 'delete_time';

    const CREATED_AT = 'create_time';

    const UPDATED_AT = 'update_time';

    public function menus()
    {
        return $this->belongsToMany(
            EgMenu::class,
            EgRoleMenu::class,
            'role_id',
            'menu_id',
        );
    }

    public function depts()
    {
        return $this->belongsToMany(
           EgDept ::class,
            EgRoleDept::class,
            'role_id',
            'dept_id',
        );
    }



}
