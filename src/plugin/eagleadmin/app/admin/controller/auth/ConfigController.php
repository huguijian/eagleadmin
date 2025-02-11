<?php

namespace plugin\eagleadmin\app\admin\controller\auth;

use plugin\eagleadmin\app\model\EmsConfig;
use plugin\eagleadmin\app\BaseController;
use plugin\eagleadmin\app\model\EmsNotice;
use plugin\eagleadmin\app\model\EmsUser;
use support\Request;
use support\Db;
use Tinywan\Jwt\JwtToken;

/**
 * 配置管理
 */
class ConfigController extends BaseController
{

    protected $noNeedAuth = ['getNotice','knowNotice'];
    /**
     * 更新配置
     * @param Request $request
     * @return \support\Response
     */
    public function updateConfig(Request $request)
    {
        $all = $request->all();
        // 站点名称变更了，更新前端配置文件
        $sysTitle = $all['value']['sys_title'] ?? '';
        $config = EmsConfig::where("key", "SYS_CONFIG")->first();
        if ($config && $sysTitle && $sysTitle != $config['sys_title']) {
            $filePath = base_path().'/plugin/ems/public/admin/config.js';
            if (file_exists($filePath)) {
                $content = file_get_contents($filePath);
                $pattern = '/sysTitle\s*:\s*"[^"]+",/u';
                $replacement = 'sysTitle: "' . $sysTitle . '",';
                $newContent = preg_replace($pattern, $replacement, $content);
                file_put_contents($filePath, $newContent);
            }
        }
        EmsConfig::updateOrCreate(["key"=>"SYS_CONFIG"],["value"=>json_encode($all["value"])]);
        return $this->success([]);
    }


    /**
     * 获取当前系统配置
     * @param Request $request
     * @return \support\Response
     */
    public function getConfig(Request $request)
    {
        $all = $request->all();
        $config = EmsConfig::where(["key"=>"SYS_CONFIG"])->first();
        $config["value"] = json_decode($config["value"]??"",true);
        return $this->success($config);
    }


    /**
     * 系统公告
     * @param Request $request
     * @return \support\Response
     * @throws \Exception
     */
    public function notice(Request $request)
    {
        $params = $request->all();
        if($request->method()=='GET') {
            $notice = EmsNotice::orderBy('id','desc')->first();
            return $this->success($notice);
        } else {
            Db::beginTransaction();
            try {
                $notice = EmsNotice::orderBy('id','desc')->first();

                if ($notice) {
                    EmsNotice::where('id',$notice->id)->update([
                        'type' => $params['type'],
                        'content'=> $params["content"],
                    ]);
                }else{
                    EmsNotice::insert([
                        'type' => $params['type'],
                        'content'=> $params["content"],
                    ]);
                }

                if ($params['type']==1) {
                    EmsUser::query()->update([
                        'is_notice' => 1
                    ]);
                }
                Db::commit();
            }catch (\Exception $e) {
                Db::rollBack();
                throw $e;
            }
            return $this->success([]);
        }
    }

    /**
     * 公告信息确认
     * @param Request $request
     * @return \support\Response
     */
    public function knowNotice(Request $request)
    {
        $params = $request->all();
        $userId = JwtToken::getCurrentId();
        EmsUser::where('id',$userId)->update([
            'is_notice' => 0
        ]);
        return $this->success([]);
    }

    /**
     * 获取公告信息
     * @param Request $request
     * @return \support\Response
     */
    public function getNotice(Request $request)
    {
        $notice = EmsNotice::orderBy('id','desc')->first();
        $isNotice = 0;
        if ($notice) {
            if ($notice['type']==2) {
                $isNotice = 1;
            }else{
                $userId = JwtToken::getCurrentId();
                $userInfo = EmsUser::where('id',$userId)->first();
                $isNotice = $userInfo['is_notice'];
            }
        }

        return $this->success([
            'is_notice' => $isNotice,
            'notice_content' => $notice['content'] ?? ''
        ]);
    }

    public function uploadLogo(Request $request)
    {
        $file = $request->file('file');
        if (!$file) {
            tips('file必传!');
        }
        $type = $request->input('type');
        $typeMap = [
            'qr_logo',
            'logo',
            'fe_logo',
        ];
        if (!in_array($type, $typeMap)) {
            tips('type只能是qr_logo、logo、fe_logo');
        }
        $path = '/upload/' . $type.'.png';
        $fileFullPath = public_path() . $path;
        $file->move($fileFullPath);
        return $this->success(['path' => $path]);
    }
}
