<?php

namespace plugin\eagleadmin\app\service;

use CURLFile;
use plugin\eagleadmin\app\model\EgAttachment;
use plugin\eagleadmin\app\model\EmsAttachment;
use Shopwwi\WebmanFilesystem\FilesystemFactory;
use support\Log;

class CommonService
{
    /**
     * 上传文件至本地
     * @param $params
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|EmsAttachment
     * @throws \League\Flysystem\FilesystemException
     */
    public function upload($params,$app='')
    {
        try {
            $file = $params['file'];
            $size = $file->getSize();
            $ext = $file->getUploadExtension();
            $type = empty($app)?:'common';

            $filesystem = FilesystemFactory::get('public');
            $stream = fopen($file->getRealPath(), 'r+');

            $md5 = md5_file($file->getRealPath());

            $path = '/upload/' . $type . '/' . $md5 . '.' . $ext;

            // 文件已存在直接返回
            $fileInfo = EgAttachment::where('md5_file', $md5)
                ->first();
            if ($fileInfo && file_exists(public_path().'/'.$fileInfo['path'])) {
                return $fileInfo;
            }

            $name = $file->getUploadName();

            // 上传到文件系统
            $filesystem->writeStream(
                $path,
                $stream
            );

            fclose($stream);
            // 入库
            return EgAttachment::create([
                'file_name' => $name,
                'md5_file' => $md5,
                'ext' => $ext,
                'path' => $path,
                'size' => $size,
                'type' => $type,
            ]);

        } catch(\Exception $e) {
            Log::error("文件上传失败！". $e->getMessage());
            throw $e;
        }
    }
}
