<?php

namespace plugin\eagleadmin\app;

use plugin\eagleadmin\app\exception\ValidateException;
use think\Validate;

class BaseValidate extends Validate
{
    protected string $method = '';

    /**
     * 是否GET请求
     */
    public function isGet(): bool|static
    {
       if (request()->method()!='GET') {
            throw new ValidateException('请求类型错误，请用GET方式请求!');
       }
       $this->method = 'GET';
       return $this;
    }

    /**
     * 是否POST请求
     */
    public function isPost(): bool|static
    {
        if (request()->method()!='POST') {
            throw new ValidateException('请求类型错误，请用POST方式请求!');
        }
        $this->method = 'POST';
        return $this;
    }

    /**
     * 验证规则
     * @param string $scene
     * @param array $validateData
     * @return array|null
     * @throws ValidateException
     */
    public function validate(string $scene='', array $validateData=[]): ?array
    {
        if (request()->method() == 'GET') {
           $params = request()->get();
       } else {
           $params = request()->post();
           $params = array_merge($params,request()->file());
       }

       $params = array_merge($params,$validateData);
        //场景
        if ($scene) {
            $result = $this->scene($scene)->check($params);
        } else {
            $result = $this->check($params);
        }

        if (!$result) {
            $exception = is_array($this->error) ? implode(';', $this->error) : $this->error;
            throw new ValidateException($exception);
        }
        return $params;
    }
}
