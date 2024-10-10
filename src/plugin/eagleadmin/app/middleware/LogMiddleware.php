<?php


namespace plugin\eagleadmin\app\middleware;

use plugin\eagleadmin\app\model\EgLog;
use plugin\eagleadmin\app\model\EmsLog;
use Tinywan\Jwt\JwtToken;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;
use support\Log;
use app\service\SysUserService;

class LogMiddleware implements MiddlewareInterface
{
    public function process(Request $request, callable $handler) : Response
    {
        $startTime = microtime(true);
        $response = $handler($request);
        $executionTime = round(microtime(true) - $startTime,3);

        $slow_query = $executionTime > 5 ? 'slow_query' : ''; //标示超过5秒的

        $content = json_decode($response->rawBody(),true);
        $content = empty($content)?[]:$content;
        if(isset($content['data'])){ //data内容太长的做下截取
            $content['data'] = json_encode($content['data'],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
            if(mb_strlen($content['data'],'utf-8')>255){ //生产环境做返回值截取操作
                $content['data'] = mb_substr($content['data'],0,255,'utf-8') . '......';
            }else{
                $content['data'] = json_decode($content['data'],true);
            }
        }

        if (is_array($content)) {
            $output = array_merge($content, [
                'time_consuming' => $executionTime.'s '.$slow_query,
            ]);
        }else{
            $output = [
                'time_consuming' => $executionTime.'s '.$slow_query,
            ];
        }
        $sessionId = $request->sessionId();
        $url       =  \trim($request->fullUrl());

        $input = json_encode($request->all(),JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        $output = json_encode($output,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        $authorization = $request->header('authorization');
        if (!$authorization) {
            $userId = 0;
            if ($request->action=='login') {
                $body = json_decode($response->rawBody(),true);
                $userId = $body['data']['userInfo']['id']??0;
            }
        } else {
            $userId = getUid();
        }


        //读取注解描述信息
        $class = new \ReflectionClass($request->controller);
        $method = $class->getMethod($request->action);
        $methodAnnotations = $method->getDocComment();
        $parseAnnotations = function($annotations) {
            preg_match_all('/@(\w+)\s*(?:\((\'.*\'|.*)\))?/', $annotations, $matches);
            $annotations = array_combine($matches[1], $matches[2]);
            return $annotations;
        };

        $parseMethod = $parseAnnotations($methodAnnotations);
        $logInfo   = [
            "ip"     => $request->getRealIp(),
            "request_id" => $sessionId,
            "app" => $request->app,
            "controller" => $request->controller,
            "action" => $request->action,
            "action_desc" =>$parseMethod['log']??'',
            "method"     => $request->method(),
            "url"    => $url,
            "input"  => (mb_strlen($input))>=65533?mb_substr($input,0,10000):$input,
            "output" => (mb_strlen($output))>=65533?mb_substr($output,0,10000):$output,
            "cost_time" => (float)($executionTime),
            "user_id" => $userId,
            "user_agent" => $request->header()['user-agent']??''
        ];


        if (!empty($content) && (!isset($content["type"]) || $content["type"]!="failed")) {
            Log::info("request_id:{$sessionId} 操作日志 ",$logInfo);
        }

        //记录后台操作日志
        EgLog::insert($logInfo);
        return $response;
    }
}
