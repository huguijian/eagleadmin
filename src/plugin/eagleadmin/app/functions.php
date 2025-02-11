<?php
/**
 * Here is your custom functions.
 */
use support\Response;
use support\Log;
use plugin\eagleadmin\app\exception\BusinessException as MyBusinessException;
use Tinywan\Jwt\JwtToken;
use Tinywan\Jwt\Exception\JwtTokenExpiredException;

/**
 * 当前管理员
 * @param null|array|string $fields
 * @return array|mixed|null
 */
if (!function_exists("admin")) {
    function admin($fields = null)
    {

        if (!$admin = Tinywan\Jwt\JwtToken::getExtend()) {
            return null;
        }
        if ($fields === null) {
            return $admin;
        }
        if (is_array($fields)) {
            $results = [];
            foreach ($fields as $field) {
                $results[$field] = $admin[$field] ?? null;
            }
            return $results;
        }
        return $admin[$fields] ?? null;
    }
}


if (!function_exists("admin_id")) {
    function admin_id()
    {
        return admin('id');
    }
}


/**
 * 发送模板短信
 * @param to 手机号码集合,用英文逗号分开
 * @param datas 内容数据 格式为数组 例如：array('Marry','Alon')，如不需替换请填 null
 * @param $tempId 模板Id
 */
if (!function_exists("send_sms")) {
    /**
     * @throws LogErrorException
     */
    function send_sms($to, $datas, $tempId) {
        //主帐号
        $accountSid   = env("SMS_ACCOUNT_SID");
        //主帐号Token
        $accountToken = env("SMS_ACCOUNT_TOKEN");
        //应用Id
        $appId = env("SMS_APP_ID");
        //请求地址，格式如下，不需要写https://
        $serverIP = 'app.cloopen.com';
        //请求端口
        $serverPort = '8883';
        //REST版本号
        $softVersion = '2013-12-26';
        // 初始化REST SDK
        $rest = new \plugin\eagleadmin\extend\sms\SDK\REST($serverIP, $serverPort, $softVersion);
        $rest->setAccount($accountSid, $accountToken);
        $rest->setAppId($appId);

        // 发送模板短信
        //echo "Sending TemplateSMS to $to <br/>";
        $result = $rest->sendTemplateSMS($to, $datas, $tempId);
        if ($result == NULL) {
            throw new \support\exception\BusinessException("sms result error!");
        }
        if ($result->statusCode != 0) {
            throw new \support\exception\BusinessException("sms error code:".$result->statusCode." msg:".$result->statusMsg);
        }
        return true;
    }
}



if (function_exists("get_mini_config")) {
    function get_mini_config(): array
    {
        return [
            'app_id' => env("WX_MINI_APP_ID"),
            'secret' => env("WX_MINI_SECRET"),
            'token'  => env("WX_MINI_TOKEN"),
            'aes_key' => '', // 明文模式请勿填写 EncodingAESKey
            'http' => [
                'throw'  => true, // 状态码非 200、300 时是否抛出异常，默认为开启
                'timeout' => 5.0,
                // 'base_uri' => 'https://api.weixin.qq.com/', // 如果你在国外想要覆盖默认的 url 的时候才使用，根据不同的模块配置不同的 uri

                'retry' => true, // 使用默认重试配置
                //  'retry' => [
                //      // 仅以下状态码重试
                //      'http_codes' => [429, 500]
                //       // 最大重试次数
                //      'max_retries' => 3,
                //      // 请求间隔 (毫秒)
                //      'delay' => 1000,
                //      // 如果设置，每次重试的等待时间都会增加这个系数
                //      // (例如. 首次:1000ms; 第二次: 3 * 1000ms; etc.)
                //      'multiplier' => 3
                //  ],
            ],
        ];
    }
}

if (!function_exists("success")) {
    function success($data = [], $msg = 'success', $code = 0): Response
    {
        throw new MyBusinessException($msg ?? '', $data, $code);
    }
}

if (!function_exists("tips")) {
    function tips($msg = 'fail', $code = -1, $data = []): Response
    {
        throw new MyBusinessException($msg ?? '', $data, $code);
    }
}

if (!function_exists("codeMsg")) {
    function codeMsg($code, $msg) {
        return [
            'code' => $code,
            'msg' => $msg,
        ];
    }
}

/**
 * 生成随机手机号验证码
 */
if (!function_exists("rand_verify_num")) {
    function rand_verify_num($count = 4) {
        $num = "";
        for ($i=0;$i<$count;$i++) {
            $num .= rand(0,9);
        }

        return $num;
    }
}

if (!function_exists('getUid')) {
    function getUid() {
        try {
            $uid = JwtToken::getCurrentId();
        } catch (\Throwable $e) {
            if ($e instanceof JwtTokenExpiredException ) {
                Log::info('---会话已过期---', [request()->header()]);
                return new Response(401, ['Content-Type' => 'application/json'],
                                \json_encode(['msg' => $e->getMessage(), 'code' => 401], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            }
            throw $e;
        }
        return $uid;
    }
}

if (!function_exists('getUserInfo')) {
    function getUserInfo()
    {
        try {
            $userInfo = JwtToken::getExtend();
        } catch (\Throwable $e) {
        }
        return $userInfo ?? null;
    }
}
