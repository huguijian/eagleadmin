<?php
namespace plugin\eagleadmin\process;

use Workerman\Crontab\Crontab;
use plugin\eagleadmin\app\model\EgCrontab;
use plugin\eagleadmin\app\admin\logic\CrontabLogic;

class Task
{
    public function onWorkerStart()
    {

        $taskList = EgCrontab::where('status', 1)->get();
        foreach ($taskList as $item) {
            new Crontab($item->rule, function () use ($item) {
                CrontabLogic::run($item->id);
            });
        }
    }

    public function run($args)
    {
        echo '任务调用：'.date('Y-m-d H:i:s')."\n";
        var_dump('参数:'. $args);
        return '任务调用：'.date('Y-m-d H:i:s')."\n";
    }
}