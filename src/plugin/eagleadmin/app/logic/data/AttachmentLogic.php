<?php
namespace plugin\eagleadmin\app\logic\data;
use plugin\eagleadmin\app\logic\ILogic;
use plugin\eagleadmin\app\model\EgAttachment;

class AttachmentLogic extends ILogic
{
    public function __construct()
    {
        $this->model = new EgAttachment();
    }

    /**
     * 附件列表
     * @param mixed $request
     * @return array
     */
    public function select($request)
    {
        $getExtensionsByType = function($type) {
            $fileTypes = [
                // 图片文件
                'jpg' => 'image',
                'jpeg' => 'image',
                'png' => 'image',
                'gif' => 'image',
                'bmp' => 'image',
                'webp' => 'image',

                // 音频文件
                'mp3' => 'audio',
                'wav' => 'audio',
                'ogg' => 'audio',
                'aac' => 'audio',

                // 视频文件
                'mp4' => 'video',
                'avi' => 'video',
                'mov' => 'video',
                'mkv' => 'video',
                'flv' => 'video',

                // 文档文件
                'pdf' => 'document',
                'doc' => 'document',
                'docx' => 'document',
                'xls' => 'document',
                'xlsx' => 'document',
                'ppt' => 'document',
                'pptx' => 'document',
                'txt' => 'document',
            ];

            $extensions = [];
            foreach ($fileTypes as $extension => $fileType) {
                if ($fileType === $type) {
                    $extensions[] = $extension;
                }
            }
            return $extensions;
        };


        $mineType = $getExtensionsByType($request->get('mime_type'));
        $storageMode = $request->get('storage_mode');
        $createTime = $request->get('create_time');
        $this->whereArr = [
            ['field'=>'ext','opt'=>'in','val'=>$mineType],
            ['field'=>'storage_mode','opt'=>'=','val'=>$storageMode],
            ['field'=>'file_name','opt'=>'like','val'=>'%'.$request->get('file_name').'%'],
            ['field'=>'create_time','opt'=>'between','val'=>[$createTime[0]??'',$createTime[1]??'']]
         ];

        return parent::select($request);
    }
}