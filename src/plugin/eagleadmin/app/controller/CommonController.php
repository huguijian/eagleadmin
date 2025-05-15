<?php

namespace plugin\eagleadmin\app\controller;

use plugin\eagleadmin\app\BaseController;
use support\Request;
use plugin\eagleadmin\app\UploadValidator;
use plugin\eagleadmin\app\service\CommonService;
use plugin\eagleadmin\app\service\UploadService;

class CommonController extends BaseController
{
    protected $noNeedAuth = ['upload','checkFile','uploadChunks','mergeChunks'];
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

    /**
     * 检查文件是否存在（用于秒传）
     */
    public function checkFile(Request $request)
    {
        $hash = $request->get('hash');
        $fileName = $request->get('fileName');
        
        if (empty($hash) || empty($fileName)) {
            return json(['code' => -1, 'message' => '参数错误']);
        }
        
        $service = new UploadService();
        $result = $service->checkFile($hash, $fileName);
        
        return json($result);
    }
    
    /**
     * 上传文件或分片
     */
    public function uploadChunks(Request $request)
    {
        $service = new UploadService();
        $result = $service->upload($request);
        
        return json($result);
    }
    
    /**
     * 合并分片
     */
    public function mergeChunks(Request $request)
    {
        $hash = $request->get('hash');
        $fileName = $request->get('fileName');
        $chunkTotal = (int)$request->get('chunkTotal', 0);
        
        if (empty($hash) || empty($fileName) || $chunkTotal <= 0) {
            return json(['code' => 1, 'message' => '参数错误']);
        }
        
        $service = new UploadService();
        $result = $service->mergeChunks($hash, $fileName, $chunkTotal);
        
        return json($result);
    }
}
