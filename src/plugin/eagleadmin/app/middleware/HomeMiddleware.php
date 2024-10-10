<?php
namespace plugin\eagleadmin\app\middleware;

use app\admin\service\SysUserService;
use support\exception\BusinessException;
use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;

class HomeMiddleware implements MiddlewareInterface
{

    /**
     * @throws BusinessException
     * @throws \ReflectionException
     */
    public function process(Request $request, callable $handler) : Response
    {
        $controller = $request->controller;
        $action     = $request->action;

        // 获取控制器鉴权信息
        $class = new \ReflectionClass($controller);
        $properties = $class->getDefaultProperties();
        $noNeedLogin = $properties['noNeedLogin'] ?? [];


        // 不需要登录
        if (in_array($action, $noNeedLogin) || in_array('*', $noNeedLogin)) {
            $response = $request->method() == 'OPTIONS' ? response('') : $handler($request);
        }else{
            try {
                $accessUserId = \Tinywan\Jwt\JwtToken::getCurrentId();
                $response = $request->method() == 'OPTIONS' ? response('') : $handler($request);
            } catch (\Tinywan\Jwt\Exception\JwtTokenExpiredException $exception) {//token过期
                // 返回自己自定义的message格式
                $msg = $exception->getMessage();
                $code = 401;
                $response = json([
                    'code' => $code,
                    'msg'  => $msg,
                    'data' => []
                ]);
            } catch (\Tinywan\Jwt\Exception\JwtRefreshTokenExpiredException $exception) {//提交的刷新token验证失败
                // 返回自己自定义的message格式
                $msg = $exception->getMessage();
                $code = 403;
                $response = json([
                    'code' => $code,
                    'msg'  => $msg,
                    'data' => []
                ]);
            }
        }
        return $response;
    }
}
