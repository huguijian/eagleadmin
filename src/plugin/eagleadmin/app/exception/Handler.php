<?php
/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace plugin\eagleadmin\app\exception;

use Throwable;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\Exception\ExceptionHandler;

/**
 * Class Handler
 * @package Support\Exception
 */
class Handler extends ExceptionHandler
{

    public function __construct($logger, $debug)
    {
        parent::__construct($logger, $debug);
        $this->dontReport = [
            ValidateException::class,
            BusinessException::class,
        ];
    }

    public function render(Request $request, Throwable $exception): Response
    {
        $code = $exception->getCode();
        $httpStatus = 500;
        if ($exception instanceof ValidateException) {
            $json = ['code' => $code ? $code : -1, 'msg' =>  $exception->getMessage(), 'type' => 'failed'];
            $httpStatus = 200;
            return new Response($httpStatus, ['Content-Type' => 'application/json'],
                \json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        } elseif (($exception instanceof BusinessException) && ($response = $exception->render($request))) {
            $httpStatus = 200;
            return $response;
        } 

        $code = $exception->getCode();
        $json = ['code' => $code ? $code : 500, 'msg' => $this->_debug ? $exception->getMessage() : 'Server internal error', 'type' => 'failed'];
        $this->_debug && $json['traces'] = \nl2br((string)$exception);
        return new Response($httpStatus, ['Content-Type' => 'application/json'],
                \json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }


    public function report(Throwable $exception)
    {
        if ($this->shouldntReport($exception)) {
            return;
        }
        $logs      = [];
        $sessionId = '';
        if ($request = \request()) {
            $sessionId = $request->sessionId();
            $logs = [
                "ip"     => $request->getRealIp(),
                "method" => $request->method(),
                "url"    => \trim($request->fullUrl()),
                "input"  => $request->all(),
                "out"    => PHP_EOL.$exception
            ];
        }
        $this->logger->error("request_id:{$sessionId} 系统异常 ",$logs);
    }
}
