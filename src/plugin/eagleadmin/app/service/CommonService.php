<?php

namespace plugin\eagleadmin\app\service;

use CURLFile;
use plugin\eagleadmin\app\model\EmsAttachment;
use Shopwwi\WebmanFilesystem\FilesystemFactory;
use support\Log;

class CommonService
{
    public function upload($params)
    {
        try {
            $file = $params['file'];
            $name = $file->getUploadName();
            $size = $file->getSize();
            $ext = $file->getUploadExtension();
            $type = $params['type'] ?? 'common';
            $fileFullPath = $file->getRealPath();
            $curlFile = new CURLFile($fileFullPath);
            $md5 = md5_file($fileFullPath);
            $path = '/upload/' . $type . '/' . $md5 . '.' . $ext;
            // 文件已存在直接返回
            $fileInfo = EmsAttachment::where('md5_file', $md5)
                ->first();
            if ($fileInfo) {
                return $fileInfo;
            }

            $wechatAgentHost = env('WECHAT_TCP_SERVER_HOST', '');
            if (!$wechatAgentHost) {
                tips('配置错误！');
            }
            $url = 'http://'. $wechatAgentHost . '/admin/admin/upload-img';
            $data = [
                'file' => $curlFile,
                'type' => $type,
                'ext' => $ext,
                'secret' => 'rqp3a7EYFZl1jGyf',
            ];
            $res = curlRun($url, $data, [], 'POST');
            if ($res) {
                return EmsAttachment::create([
                    'file_name' => $name,
                    'md5_file' => $md5,
                    'ext' => $ext,
                    'path' => $path,
                    'size' => $size,
                    'type' => EmsAttachment::TYPE[$type] ?? 0,
                ]);
            }
            tips('上传失败!');
        } catch(\Exception $e) {
            Log::error("文件上传失败！". $e->getMessage());
            throw $e;
        }
    }


    /**
     * 上传文件至本地
     * @param $params
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|EmsAttachment
     * @throws \League\Flysystem\FilesystemException
     */
    public function uploadLocal($params)
    {
        try {
            $file = $params['file'];
            $size = $file->getSize();
            $ext = $file->getUploadExtension();
            $type = $params['type'] ?? 'common';

            $filesystem = FilesystemFactory::get('public');
            $stream = fopen($file->getRealPath(), 'r+');

            $md5 = md5_file($file->getRealPath());

            $path = '/upload/' . $type . '/' . $md5 . '.' . $ext;

            // 文件已存在直接返回
            $fileInfo = EmsAttachment::where('md5_file', $md5)
                ->first();
            if ($fileInfo) {
                return $fileInfo;
            }

            $name = $file->getUploadName();

            // 上传到文件系统
            $filesystem->writeStream(
                $path,
                $stream
            );

            if (strtolower($ext) == 'jpeg' || strtolower($ext) == 'jpg' || strtolower($ext) == 'png' || strtolower($ext) == 'gif') {
                // 创建图像资源
                if(strtolower($ext) == 'jpeg' || strtolower($ext) == 'jpg'){
                    $image = imagecreatefromjpeg(public_path($path));
                } elseif(strtolower($ext) == 'png'){
                    $image = imagecreatefrompng(public_path($path));
                    imagesavealpha($image, true);
                } elseif(strtolower($ext) == 'gif'){
                    $image = imagecreatefromgif(public_path($path));
                }
                // 压缩图片大小
                //$compressedImage = imagescale($image, 480,500); // 将图片调整为宽度为800像素，高度按比例缩放
                // 保存压缩后的图片
                $savePath = public_path($path);
                //imagejpeg($compressedImage, $savePath, 80); // 保存为JPEG格式，质量为80
                // 释放资源
                imagedestroy($image);
                //imagedestroy($compressedImage);

            }
            fclose($stream);
            // 入库
            return EmsAttachment::create([
                'file_name' => $name,
                'md5_file' => $md5,
                'ext' => $ext,
                'path' => $path,
                'size' => $size,
                'type' => EmsAttachment::TYPE[$type] ?? 0,
            ]);
        } catch(\Exception $e) {
            Log::error("文件上传失败！". $e->getMessage());
            throw $e;
        }
    }

}
