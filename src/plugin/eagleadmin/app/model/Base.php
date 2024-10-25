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

}
