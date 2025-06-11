<?php

namespace plugin\eagleadmin\app\service;

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

            $filesystem = FilesystemFactory::get('public');
            $stream = fopen($file->getRealPath(), 'r+');

            $md5 = md5_file($file->getRealPath());

            $path = '/upload/' . $app . '/' . $md5 . '.' . $ext;

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
                'app' => $app,
                'storage_mode' => 1,
            ]);

        } catch(\Exception $e) {
            Log::error("文件上传失败！". $e->getMessage());
            throw $e;
        }
    }

     /**
     * 自定义上传
     * @param mixed $params
     * @param mixed $app
     * @return object|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|\support\Model
     */
    public function customUpload($params,$dir='')
    {
        try {
            $dir = rtrim($dir,'/').'/';
            $file = $params['file'];
            $size = $file->getSize();
            $ext = $file->getUploadExtension();
            $name = $file->getUploadName();
            $md5 = md5_file($file->getRealPath());
            $path = $dir.$name;

            // 文件已存在直接返回
            $fileInfo = EgAttachment::where('md5_file', $md5)
                ->first();
            if ($fileInfo && file_exists($fileInfo['path'])) {
                return $fileInfo;
            }

            if ($file && $file->isValid()) {
                $file->move($path);
            }

            // 入库
            return EgAttachment::create([
                'file_name' => $name,
                'md5_file' => $md5,
                'ext' => $ext,
                'path' => $path,
                'url'  => $path,
                'size' => $size,
                'storage_mode' => 1,
            ]);

        } catch(\Exception $e) {
            Log::error("文件上传失败！". $e->getMessage());
            throw $e;
        }
    }
}
