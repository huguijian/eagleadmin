<?php
namespace Plugin\eagleadmin\app\logic;

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
        call_user_func_array([$this->model,$name],$arguments);
    }
}