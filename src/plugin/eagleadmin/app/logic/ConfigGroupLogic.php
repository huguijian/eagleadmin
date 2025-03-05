<?php
namespace plugin\eagleadmin\app\logic;

use plugin\eagleadmin\app\model\EgSystemConfigGroup;

class ConfigGroupLogic extends ILogic
{
    public function __construct()
    {
        $this->model = new EgSystemConfigGroup();
    }
}