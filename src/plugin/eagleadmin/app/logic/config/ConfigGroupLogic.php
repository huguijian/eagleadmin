<?php
namespace plugin\eagleadmin\app\logic\config;
use plugin\eagleadmin\app\logic\ILogic;
use plugin\eagleadmin\app\model\EgSystemConfigGroup;

class ConfigGroupLogic extends ILogic
{
    public function __construct()
    {
        $this->model = new EgSystemConfigGroup();
    }
}