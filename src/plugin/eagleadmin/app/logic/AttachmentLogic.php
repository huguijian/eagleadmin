<?php
namespace plugin\eagleadmin\app\logic;

use plugin\eagleadmin\app\model\EgAttachment;

class AttachmentLogic extends ILogic
{
    public function __construct()
    {
        $this->model = new EgAttachment();
    }

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

        if ($mineType) {
            $this->whereArr = [
                ['field'=>'ext','opt'=>'in','val'=>$mineType]
            ];
        }

        return parent::select($request);
    }
}