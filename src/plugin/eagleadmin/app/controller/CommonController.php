<?php

namespace plugin\eagleadmin\app\controller;

use plugin\eagleadmin\app\BaseController;
use support\Request;
use plugin\eagleadmin\app\UploadValidator;
use plugin\eagleadmin\app\service\CommonService;

class CommonController extends BaseController
{
    /**
     * 文件资源上传
     */
    public function upload(Request $request)
    {
        $params = $request->all();
        $params['file']  = $request->file('file');
        $params = (new UploadValidator())->isPost()->validate('',[
            'file' => $params['file'],
            'size' => $params['file']->getSize(),
            'ext'  => $params['file']->getUploadExtension(),
            'type' => $params['type'] ?? '',
        ]);
        $fileInfo = (new CommonService())->upload($params,$params['app']??'eagleadmin');
        $fileInfo = collect($fileInfo)->toArray();
        if ($fileInfo) {
            return $this->success($fileInfo, '上传成功!');
        }
        return $this->error('上传失败!');
    }
}
