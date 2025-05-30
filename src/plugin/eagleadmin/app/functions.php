<?php
/**
 * Here is your custom functions.
 */
use support\Response;
use support\Log;
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

if (!function_exists("get_mini_config")) {
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
