<?php

return [
    // 上传目录
    'path' => public_path() . '/uploads',
    // 分片目录
    'chunk_path' => public_path() . '/uploads/chunks',
    // 文件目录
    'file_path' => public_path() . '/uploads/files',
    // 允许的文件类型
    'allowed_types' => '*',  // 可以设置为特定类型，如 'jpg,png,pdf,doc,docx,xls,xlsx,zip'
    // 最大文件大小 (字节)，默认1GB
    'max_size' => 1024 * 1024 * 1024*10,
    // 分片大小 (字节)，默认2MB
    'chunk_size' => 2 * 1024 * 1024,
];