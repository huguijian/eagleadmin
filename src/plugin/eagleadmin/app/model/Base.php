<?php

namespace plugin\eagleadmin\app\model;

use DateTimeInterface;
use support\Model;
use jwt\JwtInstance;
use Tinywan\Jwt\JwtToken;

class Base extends Model
{
    /**
     * 格式化日期
     *
     * @param DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            // 如果有user_id列，创建时自动录入值
            if ($model->getConnection()
                    ->getSchemaBuilder()
                    ->hasColumn($model->getTable(), 'user_id')) {
                $model->user_id = JwtToken::getCurrentId();
            }
        });
    }

}
