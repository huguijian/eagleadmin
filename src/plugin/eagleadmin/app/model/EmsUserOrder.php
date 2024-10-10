<?php

namespace plugin\eagleadmin\app\model;

class EmsUserOrder extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ems_user_order';


    const CREATED_AT = 'create_time';

    const UPDATED_AT = 'update_time';

    public function deviceInfo()
    {
        return $this->hasOne(EmsDevice::class, 'id', 'device_id');
    }

    public function userInfo()
    {
        return $this->hasOne(EmsUser::class,'id', 'user_id');
    }

    public function subjectInfo()
    {
        return $this->hasOne(EmsSubject::class,'id', 'subject_id');
    }

    public function orderLog()
    {
        return $this->hasMany(EmsUserOrderLog::class, 'order_id', 'id');
    }

    // 新的
    const STATUS = [
        '待审核' => 10,
        '审核不通过' => 20,
        '取消预约' => 30,
        '待上机' => 40,
        '上机中' => 50,
        '已下机' => 60,
    ];

    const IS_AUDIT = [
        '待审核' => 0,
        '已审核' => 1,
    ];

    /* 原来
    const STATUS = [
        '待上机' => 0,
        '上机中' => 1,
        '已下机' => 2,
    ];
    const IS_AUDIT = [
        '待审核' => 1,
        '已审核' => 2,
        '审核不通过' => 3,
        '取消预约' => 4,
    ];
    */

    const COST_TYPE = [
        '按预约时长' => 1,
        '按实际时长' => 2,
        '两者间最大时长' => 3,
    ];

}
