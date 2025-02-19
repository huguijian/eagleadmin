<?php

namespace plugin\eagleadmin\app\admin\logic;

use plugin\eagleadmin\app\model\EgCrontab;
use plugin\eagleadmin\app\model\EgCrontabLog;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class CrontabLogic
{
    /**
     * 执行定时任务
     * @param $id
     * @return bool
     */
    public static function run($id): bool
    {
        $info = EgCrontab::find($id);
        $data['crontab_id'] = $info->id;
        $data['name'] = $info->name;
        $data['target'] = $info->target;
        $data['parameter'] = $info->parameter;
        switch ($info->type) {
            case 1:
                // URL任务GET
                $httpClient = new Client([
                    'timeout' => 5,
                    'verify' => false,
                ]);
                try {
                    $httpClient->request('GET', $info->target);
                    $data['status'] = 1;
                    EgCrontabLog::create($data);
                    return true;
                } catch (GuzzleException $e) {
                    $data['status'] = 2;
                    $data['exception_info'] = $e->getMessage();
                    EgCrontabLog::create($data);
                    return false;
                }
            case 2:
                // URL任务POST
                $httpClient = new Client([
                    'timeout' => 5,
                    'verify' => false,
                ]);
                try {
                    $res = $httpClient->request('POST', $info->target, [
                        'form_params' => json_decode($info->parameter ?? '',true)
                    ]);
                    $data['status'] = 1;
                    $data['exception_info'] = $res->getBody();
                    EgCrontabLog::create($data);
                    return true;
                } catch (GuzzleException $e) {
                    $data['status'] = 2;
                    $data['exception_info'] = $e->getMessage();
                    EgCrontabLog::create($data);
                    return false;
                }
            case 3:
                // 类任务
                $class_name = $info->target;
                $method_name = 'run';
                $class = new $class_name;
                if (method_exists($class, $method_name)) {
                    $return = $class->$method_name($info->parameter);
                    $data['status'] = 1;
                    $data['exception_info'] = $return;
                    EgCrontabLog::create($data);
                    return true;
                } else {
                    $data['status'] = 2;
                    $data['exception_info'] = '类:'.$class_name.',方法:run,未找到';
                    EgCrontabLog::create($data);
                    return false;

                }
            default:
                return false;
        }
    }
}