<?php

namespace plugin\eagleadmin\app;

class UploadValidator extends BaseValidate
{
    protected $rule =   [
        'file' => 'require',
        'size' => 'checkSize',
        'ext' => 'checkExt',
    ];

    protected $message  =   [
        'file.require' => '请选择上传文件!',
    ];

    protected function checkSize($value, $rule, $data = [], $field = '')
    {
        $size = $data['size'] ?? '';
        $maxSize = config('server.max_package_size', 10*1024*1024);
        if ($size && $size > $maxSize) {
            return '上传文件大小不能超过：' . $maxSize/1024/1024 .'MB';
        }
        return true;
    }

    protected function checkExt($value, $rule, $data = [], $field = '')
    {
        $ext = $data['ext'];
        $type = $data['type'] ?? 'image';
        if ($type == 'mix') {
            if (!in_array(strtolower($ext), ['doc', 'docx', 'xls', 'xlsx', 'pdf', 'png', 'jpg', 'jpeg', 'webp'])) {
                return '上传仅支持pdf,word,image格式';
            }
        } else {
            if (!in_array(strtolower($ext), ['png', 'jpg', 'jpeg', 'webp', 'gif'])) {
                return '上传仅支持图片格式png,jpg,jpeg,webp,gif';
            }
        }
        return true;
    }
}
