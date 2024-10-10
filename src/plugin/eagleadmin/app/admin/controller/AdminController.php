<?php

namespace plugin\eagleadmin\app\admin\controller;

use exception\BusinessException;
use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;
use jwt\JwtInstance;
use plugin\eagleadmin\app\admin\logic\AdminLogic;
use plugin\eagleadmin\app\admin\validate\AdminValidate;
use plugin\eagleadmin\app\BaseController;
use plugin\eagleadmin\app\model\EmsAttachment;
use plugin\eagleadmin\app\model\EmsMenu;
use plugin\eagleadmin\app\service\CommonService;
use plugin\eagleadmin\app\UploadValidator;
use support\Redis;
use support\Request;
use Tinywan\Jwt\JwtToken;

class AdminController extends BaseController
{

    /**
     * 不需要登录
     * @var array
     */
    protected $noNeedLogin = ['login', 'refresh','getCaptcha', 'weappUpload'];
    protected $noNeedAuth = ['weappUpload'];

    public function index()
    {
        $userInfo = JwtToken::getUser();
//        $menus = SysMenu::where(function($query){
//            $query->where('type', 'menu_dir')
//                ->orWhere('type', 'menu');
//        })->where("pid",0)->get();

        $menus = EmsMenu::orderBy("weigh","asc")->where("status",1)->get();
        $menus = AdminLogic::getTreeMenuNormal($menus);

        return $this->success([
            "adminInfo" => $userInfo,
            "menus" => $menus,
            "siteConfig" => [
                "apiUrl" => "",
                "cdnUrl" => "",
                "siteName" => "",
                "upload" => [
                    "maxsize" => 10485760,
                    "mimetype" => "jpg,png,bmp,jpeg,gif,webp,zip,rar,xls,xlsx,doc,docx,wav,mp4,mp3,txt",
                    "mode" => "local",
                    "savename" => "/storage/{topic}/{year}{mon}{day}/{filesha1}{.suffix}"
                ],
            ],
            "terminal" => [
                "installServicePort" => "8000",
                "npmPackageManager" => "pnpm",
            ]
        ]);
    }

    /**
     * 用户登录
     * @log(用户登录)
     * @throws \app\exception\BusinessException|\support\exception\BusinessException
     */
    public function login(Request $request): \support\Response
    {
        $params = (new AdminValidate())->isPost()->validate();
        $isLogin = AdminLogic::login($params,$data,$code,$msg);
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
     * @return \think\response\Json
     */
    public function getCaptcha(Request $request)
    {
        // 初始化验证码类
        $phraseBuilder = new PhraseBuilder(4, '0123456789');
        $builder = new CaptchaBuilder(null,$phraseBuilder);
        // 生成验证码
        $builder->build();
        $captchaId = uniqid();
        Redis::set("ems:captcha:code:".$captchaId, $builder->getPhrase(),'EX',300);
        // 输出验证码二进制数据
        return $this->success(["base64"=>$builder->inline(),"key"=>$captchaId]);
    }

    /**
     * 文件资源上传
     * @throws BusinessException
     * @throws \app\exception\BusinessException
     */
    public function upload(Request $request)
    {
        $params = $request->all();
        $params['file']  = $request->file('file');
        $params = (new UploadValidator())->isPost()->validate('',[
            'file' => $params['file'],
            'size' => $params['file']->getSize(),
            'ext'  => $params['file']->getUploadExtension(),
            'type' => $params['type'] ?? 'common',
        ]);
        $fileInfo = (new CommonService())->upload($params);
        $fileInfo['path'] = $fileInfo['path'] ? '//'. env('WECHAT_TCP_SERVER_HOST', '') .$fileInfo['path'] : '';
        if ($fileInfo) {
            return $this->success($fileInfo, '上传成功!');
        }
        return $this->error('上传失败!');
    }

    /**
     * 小程序端上传不校验权限
     */
    public function weappUpload(Request $request)
    {
        return $this->upload($request);
    }

    /**
     * 下载
     * @param $attachmentId
     * @return \support\Response|\Webman\Http\Response
     */
    public function download(Request $request)
    {
        $attachment = EmsAttachment::find($request->input('attachment_id'));
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

    public function refresh()
    {
        $res = JwtToken::refreshToken();
        return $this->success($res);
    }

}
