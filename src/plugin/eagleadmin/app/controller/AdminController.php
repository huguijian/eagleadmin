<?php

namespace plugin\eagleadmin\app\controller;

use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;
use plugin\eagleadmin\app\logic\AdminLogic;
use plugin\eagleadmin\app\validate\AdminValidate;
use plugin\eagleadmin\app\BaseController;
use plugin\eagleadmin\app\model\EgAttachment;
use plugin\eagleadmin\app\service\CommonService;
use plugin\eagleadmin\app\UploadValidator;
use support\Redis;
use support\Request;
use Tinywan\Jwt\JwtToken;
use Webman\Event\Event;

class AdminController extends BaseController
{

    /**
     * 不需要登录
     * @var array
     */
    protected $noNeedLogin = ['login', 'refresh','getCaptcha'];

    private $adminLogic;
    public function __construct()
    {
        $this->adminLogic = new AdminLogic();
    }
    /**
     * 用户登录
     * @param \support\Request $request
     * @return \support\Response
     */
    public function login(Request $request): \support\Response
    {
        $params = (new AdminValidate())->isPost()->validate();
        $code = 0; // 初始化状态码
        $msg = ''; // 初始化提示信息
        $data = []; // 初始化返回数据
        $isLogin = $this->adminLogic->login($params, $data, $code, $msg);
        //登录事件
        Event::dispatch('user.login', [
            'user_name' => $params['username'],
            'status' => $code,
            'msg' => $msg
        ]);

        if (!$isLogin) {
            return $this->error($msg);
        }

        return $this->success([
            'userInfo' => $data,
            'routePath' => '/admin',
        ]);
    }

    /**
     * 退出登录
     * @return \support\Response
     */
    public function logout()
    {
        return $this->success([]);
    }

    /**
     * 获取验证码
     * @param \support\Request $request
     */
    public function getCaptcha(Request $request)
    {
        // 初始化验证码类
        $phraseBuilder = new PhraseBuilder(4, '0123456789');
        $builder = new CaptchaBuilder(null,$phraseBuilder);
        // 生成验证码
        $builder->build();
        $captchaId = uniqid();
        Redis::set("eagleadmin:captcha:code:".$captchaId, $builder->getPhrase(),'EX',300);
        // 输出验证码二进制数据
        return $this->success(["base64"=>$builder->inline(),"key"=>$captchaId]);
    }

     /**
      * 文件上传
      * @param \support\Request $request
      */
     public function upload(Request $request)
    {
        $params = $request->all();
        $params['file']  = $request->file('file');
        $params = (new UploadValidator())->isPost()->validate('',[
            'file' => $params['file'],
            'size' => $params['file']->getSize(),
            'ext'  => $params['file']->getUploadExtension(),
        ]);
        $fileInfo = (new CommonService())->upload($params,$params['app']??'');
        if ($fileInfo) {
            return $this->success($fileInfo, '上传成功!');
        }
        return $this->error('上传失败!');
    }


    /**
     * 下载
     * @param $attachmentId
     * @return \support\Response|\Webman\Http\Response
     */
    public function download(Request $request)
    {
        $attachment = EgAttachment::find($request->input('attachment_id'));
        if (!$attachment) {
            return $this->error('文件不存在!');
        }
        $path = $attachment->path;
        $fileName = $attachment->file_name;
        return response()->withHeaders([
            "File-Name"    => urlencode($fileName),
            "Access-Control-Expose-Headers" => "File-Name",
            "Content-Disposition" => 'attachment;filename=' . $fileName,
            "Cache-Control" => 'max-age=0',])->download(public_path($path), $fileName);
    }

    /**
     * 刷新token
     */
    public function refresh()
    {
        $res = JwtToken::refreshToken();
        return $this->success([
            'token' => $res['access_token'],
            'refresh_token' => $res['refresh_token'],
        ]);
    }

}
