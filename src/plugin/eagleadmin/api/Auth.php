<?php
namespace plugin\eagleadmin\api;
use plugin\eagleadmin\app\model\EgRole;
use plugin\eagleadmin\app\model\EgUserRole;
use support\exception\BusinessException;
use \Tinywan\Jwt\JwtToken;
use plugin\eagleadmin\app\logic\auth\UserLogic;
use plugin\eagleadmin\app\model\EgUser;
/**
 * 对外提供的鉴权接口
 */
class Auth
{
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
     * @return bool
     * @throws \ReflectionException|BusinessException
     */
    public static function canAccess(string $controller, string $action, int &$code = 0, string &$msg = ''): bool
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

        try {
            // 支持url传token
            $token = request()->input('token');
            if ($token) {
                $token = str_replace('Bearer ', '', $token);
                $tokenInfo = JwtToken::verify(1, $token);
                $accessUserId = $tokenInfo['extend']['id'] ?? 0;
            } else {
                $accessUserId = JwtToken::getCurrentId();
            }
        } catch (\Tinywan\Jwt\Exception\JwtTokenException $exception) {//token验证失败
            // 返回自己自定义的message格式
            $msg = $exception->getMessage();
            $code = 401;
            return false;
        } catch (\Tinywan\Jwt\Exception\JwtTokenExpiredException $exception) {//token过期
            // 返回自己自定义的message格式
            $msg = $exception->getMessage();
            $code = 401;
            return false;
        } catch (\Tinywan\Jwt\Exception\JwtRefreshTokenExpiredException $exception) {//提交的刷新token验证失败
            // 返回自己自定义的message格式
            $msg = $exception->getMessage();
            $code = 403;
            return false;
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
