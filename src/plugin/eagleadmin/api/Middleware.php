<?php
namespace plugin\eagleadmin\api;

use ReflectionException;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;
use support\exception\BusinessException;

/**
 * 对外提供的鉴权中间件
 */
class Middleware implements MiddlewareInterface
{
    /**
     * 鉴权
     * @param Request $request
     * @param callable $handler
     * @return Response
     * @throws ReflectionException
     * @throws BusinessException
     */
    public function process(Request $request, callable $handler): Response
    {
        $controller = $request->controller;
        $action = $request->action;

        $code = 0;
        $httpStatus = 200;
        $msg = '';
        if (!Auth::canAccess($controller, $action,$httpStatus,$code, $msg)) {
            $response = response(json_encode(['code' => $code, 'msg' => $msg, 'type' => 'error']),$httpStatus);
        } else {
            $response = $request->method() == 'OPTIONS' ? response('') : $handler($request);
        }
        return $response;
    }

}
