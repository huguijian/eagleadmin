<?php
/**
 * 业务逻辑层接口
 * @author 胡贵建
 * @version 1.0
 */
namespace plugin\eagleadmin\app\logic;
use plugin\eagleadmin\app\Crud;
abstract class ILogic
{
    use Crud;
    /**
     * 模型回调
     * @param mixed $name
     * @param mixed $arguments
     * @return void
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->model,$name],$arguments);
    }
}