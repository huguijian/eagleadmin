<?php

namespace plugin\eagleadmin\app\logic;

<<<<<<< HEAD
use Plugin\eagleadmin\app\logic\ILogic;
=======
use plugin\eagleadmin\app\logic\ILogic;
>>>>>>> d71648c (init)
use plugin\eagleadmin\app\model\EgUser;
use Tinywan\Jwt\JwtToken;

class AdminLogic extends ILogic
{
    /**
     * 用户登录
     * @param mixed $params
     * @param mixed $data
     * @param mixed $code
     * @param mixed $msg
     * @return bool
     */
    public function login($params,&$data,&$code,&$msg): bool
    {

        $userName  = $params["user_name"]??"";
        $password  = $params["password"]??"";
        $captcha   = $params["code"]??"";
        $captchaId   = $params["captcha_id"]??"";
        //模型打印sql语句
        //Db::connection()->enableQueryLog();
        $userInfo = EgUser::with('department')->where('user_name', $userName)->first();
        //var_dump(Db::getQueryLog());

        $code = 1;
        $msg = '登录成功';
        if (empty($userInfo)) {
            $code = -1;
            $msg  = "用户不存在!";
            return false;
        }

        if (!password_verify($password, $userInfo["password"])) {
            $code = -1;
            $msg  = "用户名或密码错误!";
            return false;
        }

        if ($userInfo["status"] != 1) {
            $code = -1;
            $msg  = "该用户禁止登陆!";
            return false;
        }

        /*
        if (getenv('APP_ENV')!=='local') {
            $redisCaptcha = Redis::get("eagleadmin:captcha:code:".$captchaId);

            if (empty($redisCaptcha)) {
                $code = -1;
                $msg = '验证码错误';
                return false;
            }
            if (strtolower($redisCaptcha)!=strtolower($captcha)) {
                $code = -1;
                $msg = '验证码错误或已失效!';
                // 删除缓存
                Redis::del("eagleadmin:captcha:code:".$captchaId);
                return false;
            }
        }
        */

        //        $tmp = Redis::get("captcha:code:".$captchaId);
        //        if (strtolower($verifyCode)!=strtolower($tmp)) {
        //            throw new BusinessException("验证码错误",[],500);
        //        }
        $token = JwtToken::generateToken([
            'id' => $userInfo['id'],
            'user_name' => $userInfo['user_name'],
            'email' => $userInfo['email'],
            "avatar" => $userInfo["avatar"],
            "phone" => $userInfo["phone"],
            "dept_id" => $userInfo["dept_id"],
            "nick_name" => $userInfo["nick_name"],
        ]);
        $department = $userInfo['department'] ?? [];
        $data = [
            "token" => $token["access_token"],
            "expires_in" =>$token['expires_in'],
            "avatar" => $userInfo["avatar"],
            "id" => $userInfo["id"],
            "lastlogintime" => "",
            "nickname" => $userInfo["nick_name"],
            "refresh_token" => $token["refresh_token"],
            "username" => $userInfo["user_name"],
            "department_name" => $department['name'] ?? '',
        ];

        // 登录事件
        return true;
    }


    /**
     * 获取角色树形菜单
     * @param $data
     * @param $pid
     * @param $level
     * @return array
     */
    public function getTreeRole($data, $pid = 0, $level = 0)
    {
        $newArr = [];
        foreach ($data as $item) {
            if ($item["pid"] == $pid) {
                $item["children"] = self::getTreeMenuNormal($data, $item["id"], $level + 1);
                $newArr[] = $item;
            }
        }

        return $newArr;
    }

    /**
     * 递归树形菜单
     * @param $data
     * @param int $pid
     * @param int $level
     * @return array
     */
    public function getTreeMenuNormal($data, $pid = 0, $level = 0)
    {
        $newArr = [];
        foreach ($data as $item) {
            if ($item["pid"] == $pid) {
                $item["children"] = self::getTreeMenuNormal($data, $item["id"], $level + 1);
                $newArr[] = $item;
            }
        }

        return $newArr;
    }
}
