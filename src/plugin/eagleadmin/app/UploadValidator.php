<?php

namespace plugin\eagleadmin\app;

use plugin\eagleadmin\app\model\EmsAttachment;

class UploadValidator extends BaseValidate
{
    protected $rule =   [
        'file' => 'require',
        'size' => 'checkSize',
        'type' => 'checKType',
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

    protected function checkType($value, $rule, $data = [], $field = '')
    {
        $type = $data['type'];
        if (!isset(EmsAttachment::TYPE[$type])) {
            $keyArr = array_keys(EmsAttachment::TYPE);
            $vals = implode(',', $keyArr);
            return 'type值只能传固定的值，如需增加请联系开发，目前可能的值为：'. $vals;
        }
        return true;
    }

    protected function checkExt($value, $rule, $data = [], $field = '')
    {
        $ext = $data['ext'];
        $type = $data['type'];
        if ($type == 'material') {
            if (!in_array(strtolower($ext), ['doc', 'docx', 'xls', 'xlsx', 'pdf', 'png', 'jpg', 'jpeg'])) {
                return '物料上传仅支持pdf,word,image格式';
            }
        }
        return true;
    }
}