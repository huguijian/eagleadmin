<?php
namespace plugin\eagleadmin\app\logic;

use plugin\eagleadmin\app\model\EgConfig;

class ConfigLogic extends ILogic
{
    public function __construct()
    {
        $this->model = new EgConfig();
    }
}