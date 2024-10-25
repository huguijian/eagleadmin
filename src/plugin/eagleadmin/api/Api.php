<?php

namespace plugin\eagleadmin\api;

use plugin\eagleadmin\app\model\EgAttachment;
use plugin\eagleadmin\app\service\CommonService;
use plugin\eagleadmin\app\UploadValidator;
use support\Request;

class Api
{
    /**
     * 文件资源上传
     * @throws BusinessException
     * @throws \app\exception\BusinessException
     */
    public static function upload(Request $request)
    {
        $params = $request->all();
        $params['file']  = $request->file('file');
        $params = (new UploadValidator())->isPost()->validate('',[
            'file' => $params['file'],
            'size' => $params['file']->getSize(),
            'ext'  => $params['file']->getUploadExtension(),
        ]);
        $fileInfo = (new CommonService())->upload($params);
        if ($fileInfo) {
           return true;
        }
        return false;
    }


    /**
     * 下载
     * @param $attachmentId
     * @return \support\Response|\Webman\Http\Response
     */
    public static function download(Request $request)
    {
        $attachment = EgAttachment::find($request->input('attachment_id'));
        if (!$attachment) {
            return false;
        }
        $path = $attachment->path;
        $fileName = $attachment->file_name;
        return response()->withHeaders([
            "File-Name"    => urlencode($fileName),
            "Access-Control-Expose-Headers" => "File-Name",
            "Content-Disposition" => 'attachment;filename=' . $fileName,
            "Cache-Control" => 'max-age=0',])->download(public_path($path), $fileName);
    }
}