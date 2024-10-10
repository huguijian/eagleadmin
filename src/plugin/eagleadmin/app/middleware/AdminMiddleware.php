<?php
namespace plugin\eagleadmin\app\middleware;

use app\admin\service\SysUserService;
use support\exception\BusinessException;
use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;
use app\api\Auth;

class AdminMiddleware implements MiddlewareInterface
{

    /**
     * @throws BusinessException
     * @throws \ReflectionException
     */
    public function process(Request $request, callable $handler) : Response
    {
        $controller = $request->controller;
        $action     = $request->action;

        $code = 0;
        $msg  = '';

        if (!Auth::canAccess($controller,$action,$code,$msg)) {
            return response(json_encode([
                'code' => $code,
                'msg'  => $msg,
                'data' => []]));
        }else{
            $response = $request->method() == 'OPTIONS' ? response('') : $handler($request);
        }

        return $response;
    }
}