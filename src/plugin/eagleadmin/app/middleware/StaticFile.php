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

namespace plugin\eagleadmin\app\middleware;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;

/**
 * Class StaticFile
 * @package app\middleware
 */
class StaticFile implements MiddlewareInterface
{
    public function process(Request $request, callable $next): Response
    {
        // Access to files beginning with. Is prohibited
        if (strpos($request->path(), '/.') !== false) {
            return response('<h1>403 forbidden</h1>', 403);
        }


        // 静态文件目录
        $static_base_path = base_path().'/public';
        // 文件
        $file = $static_base_path.$request->path();
        if (!is_file($file)) {
            return response('<h1>404 Not Found</h1>', 404);
        }
        //return response('')->withFile($file);
        return response(gzencode(file_get_contents($file)))->setHeadContentTypeForFile($file)->withHeader("Content-Encoding","gzip");
    }
}
