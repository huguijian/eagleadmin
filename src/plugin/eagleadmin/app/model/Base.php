<?php

namespace plugin\eagleadmin\app\model;

use support\Model;
use DateTimeInterface;

class Base extends Model
{
    protected $connection = 'plugin.eagleadmin.mysql';

     /**
      * 日期格式化
      * @param DateTimeInterface $date
      * @return string
      */
     protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
