<?php
namespace plugin\eagleadmin\api;

use ReflectionException;
use Webman\Event\Event;
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
        $msg = '';
        if (!Auth::canAccess($controller, $action, $code, $msg)) {
            $response = json(['code' => $code, 'msg' => $msg, 'type' => 'error']);
        } else {

            $whiteList = config('plugin.eagleadmin.eagleadmin.white_list', []);
            $rule = trim(strtolower($request->path()));
            $rule = str_replace('/app/eagleadmin', '', $rule);
            if (!in_array($rule,$whiteList)) {
                Event::dispatch('user.operateLog',[]);
            }
            $response = $request->method() == 'OPTIONS' ? response('') : $handler($request);
        }
        return $response;
    }

}
