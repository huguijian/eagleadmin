<?php
namespace plugin\eagleadmin\app\model;


class EgUser extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'eg_user';

    protected $fillable = [
        'password',
        'nick_name',
        'avatar_id',
        'email',
        'remark',
        'user_name',
        'phone',
        'status',
    ];


    protected $hidden = [
        'password',
        'password_v',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'status' => 'integer'];

    const CREATED_AT = 'create_time';

    const UPDATED_AT = 'update_time';



    public function department()
    {
        return $this->belongsToMany(
            EgDepartment::class,
            'eg_user_department',
            'user_id',
            'department_id',
        );
    }

    public function roles()
    {
        return $this->belongsToMany(
                EgRole::class,
                'eg_user_role',
                'user_id',
                'role_id'
            )
            ->withPivot(['create_time','update_time']);
    }
}
