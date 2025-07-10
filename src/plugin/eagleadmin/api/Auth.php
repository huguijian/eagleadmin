<?php
namespace plugin\eagleadmin\api;
use support\exception\BusinessException;
use \Tinywan\Jwt\JwtToken;
use plugin\eagleadmin\app\logic\auth\UserLogic;
use plugin\eagleadmin\app\model\EgUser;
use Webman\Http\Response;
use Webman\Event\Event;

/**
 * 对外提供的鉴权接口
 */
class Auth
{
    const ACCESS_TOKEN = 1;
    const REFRESH_TOKEN = 2;
    /**
     * 判断权限
     * 如果没有权限则抛出异常
     * @param string $controller
     * @param string $action
     * @return void
     * @throws \ReflectionException|BusinessException
     */
    public static function access(string $controller, string $action)
    {
        $code = 0;
        $msg = '';
        if (!static::canAccess($controller, $action, $code, $msg)) {
            throw new BusinessException($msg, $code);
        }
    }

    /**
     * 判断是否有权限
     * @param string $controller
     * @param string $action
     * @param int $code
     * @param string $msg
     * @return bool|Response
     */
    public static function canAccess(string $controller, string $action, int &$httpStatus = 200,int &$code = 0, string &$msg = '') 
    {
        // 无控制器信息说明是函数调用，函数不属于任何控制器，鉴权操作应该在函数内部完成。
        if (!$controller) {
            return true;
        }
        // 获取控制器鉴权信息
        $class = new \ReflectionClass($controller);
        $properties = $class->getDefaultProperties();
        $noNeedLogin = $properties['noNeedLogin'] ?? [];
        $noNeedAuth = $properties['noNeedAuth'] ?? [];

        // 不需要登录
        if (in_array($action, $noNeedLogin)) {
            return true;
        }

        $httpStatus = 200;
        try {
            // 支持url传token
            $token = request()->input('token');
            if($action=='refreshToken'){
                $tokenType = self::REFRESH_TOKEN;
            }else{
                $tokenType = self::ACCESS_TOKEN;
            }
            if ($token) {
                $token = str_replace('Bearer ', '', $token);
                $tokenInfo = JwtToken::verify($tokenType, $token);
                $accessUserId = $tokenInfo['extend']['id'] ?? 0;
            } else {
                $tokenInfo = JwtToken::verify($tokenType);
                $accessUserId = $tokenInfo['extend']['id'] ?? 0;
            }
        } catch (\Tinywan\Jwt\Exception\JwtTokenException $exception) {//token验证失败
            // 返回自己自定义的message格式
            $msg = $exception->getMessage();
            $code = $httpStatus = 401;
            return false;
            
        } catch (\Tinywan\Jwt\Exception\JwtTokenExpiredException $exception) {//token过期
            // 返回自己自定义的message格式
            $msg = $exception->getMessage();
            $code = $httpStatus = 401;
            return false;
        }

        $whiteList = config('plugin.eagleadmin.eagleadmin.white_list', []);
        $rule = trim(strtolower(request()->path()));
        if (!in_array($rule,$whiteList)) {
            Event::dispatch('user.operateLog',[]);
        }

        // 不需要鉴权
        if (in_array($action, $noNeedAuth) || in_array('*', $noNeedAuth)) {
            return true;
        }

        // id为1的为超级管理员
        if ($accessUserId == 1) {
            return true;
        }

        $user = EgUser::where('id',admin_id())->first();
        $codes = (new UserLogic())->getCodes($user);
        $currentPath = request()->path();
        $currentPath = str_replace('/app/eagleadmin', '', $currentPath);
      
        if (!in_array($currentPath, $codes)) {
            $msg = '无权限';
            $code = 2;
            return false;
        }
       
        return true;
    }
}
