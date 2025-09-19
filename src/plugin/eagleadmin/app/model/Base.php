<?php

namespace plugin\eagleadmin\app\model;

use support\Model;
use DateTimeInterface;

class Base extends Model
{
    protected $connection = 'plugin.eagleadmin.mysql';

     protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
