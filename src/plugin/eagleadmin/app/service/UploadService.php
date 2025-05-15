<?php
namespace plugin\eagleadmin\app\service;
use plugin\eagleadmin\app\logic\config\ConfigLogic;
use support\Request;
class UploadService
{
    
    /**
     * 检查文件是否已存在（用于秒传）
     * 同时返回已上传的分片信息（用于断点续传）
     */
    public function checkFile(string $hash, string $fileName): array
    {
        // 获取配置
        $config = config('plugin.eagleadmin.upload');
        
        // 检查文件是否已存在
        $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
        $savePath = $config['file_path'] . '/' . date('Ymd');
        $filePath = $savePath . '/' . $hash . '.' . $fileExt;
        
        if (file_exists($filePath)) {
            // 文件已存在，可以秒传
            $url = '/uploads/files/' . date('Ymd') . '/' . $hash . '.' . $fileExt;
            return [
                'code' => 0,
                'message' => '文件已存在',
                'data' => [
                    'url' => $url,
                    'path' => $url,
                ]
            ];
        }
        
        // 获取已上传的分片
        $uploadedChunks = $this->getUploadedChunks($hash);
        // 文件不存在，需要上传
        return [
            'code' => 0,
            'message' => '文件不存在，需要上传',
            'data' => [
                'uploadedChunks' => $uploadedChunks
            ]
        ];
    }

    /**
     * 获取已上传的分片列表
     * 
     * @param string $hash 文件哈希值
     * @return array 已上传的分片索引列表
     */
    public function getUploadedChunks(string $hash): array
    {
        $config = config('plugin.eagleadmin.upload');
        $chunkDir = $config['chunk_path'] . '/' . $hash;
        
        if (!is_dir($chunkDir)) {
            return [];
        }
        
        $uploadedChunks = [];
        $files = scandir($chunkDir);
        
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                $uploadedChunks[] = (int)$file;
            }
        }
        
        return $uploadedChunks;
    }
    
    /**
     * 上传文件或分片
     * 
     * @param Request $request
     * @return array
     */
    public function upload(Request $request): array
    {
        // 获取配置
        $config = config('plugin.eagleadmin.upload');
        
        // 获取上传文件
        $file = $request->file('file');
        if (!$file || !$file->isValid()) {
            return ['code' => -1, 'message' => '未找到有效的上传文件'];
        }
        
        // 检查文件大小
        if ($request->post('fileSize') > $config['max_size']) {
            return ['code' => -1, 'message' => '文件大小超出限制'];
        }
        
        // 获取参数
        $hash = $request->post('hash');
        $fileName = $request->post('fileName');
        $isChunk = $request->post('isChunk', false);
        
        if ($isChunk) {
            // 分片上传处理
            $chunkIndex = $request->post('chunkIndex');
            $chunkTotal = $request->post('chunkTotal');
            
            // 创建分片存储目录
            $chunkDir = $config['chunk_path'] . '/' . $hash;
            if (!is_dir($chunkDir)) {
                mkdir($chunkDir, 0755, true);
            }
            
            // 保存分片
            $chunkFile = $chunkDir . '/' . $chunkIndex;
            $file->move($chunkFile);
            
            return [
                'code' => 0,
                'message' => '分片上传成功',
                'data' => [
                    'chunkIndex' => $chunkIndex,
                    'chunkTotal' => $chunkTotal,
                ]
            ];
        } else {
            // 普通文件上传处理
            $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
            $savePath = $config['file_path'] . '/' . date('Ymd');
            
            // 创建目录
            if (!is_dir($savePath)) {
                mkdir($savePath, 0755, true);
            }
            
            // 保存文件
            $saveName = $hash . '.' . $fileExt;
            $file->move($savePath . '/' . $saveName);
            
            $url = '/uploads/files/' . date('Ymd') . '/' . $saveName;
            return [
                'code' => 0,
                'message' => '文件上传成功',
                'data' => [
                    'url' => $url,
                    'path' => $url,
                ]
            ];
        }
    }
    
    /**
     * 合并分片
     * 
     * @param string $hash 文件哈希值
     * @param string $fileName 文件名
     * @param int $chunkTotal 分片总数
     * @return array
     */
    public function mergeChunks(string $hash, string $fileName, int $chunkTotal): array
    {
        // 获取配置
        $config = config('plugin.eagleadmin.upload');
    
        // 分片目录
        $chunkDir = $config['chunk_path'] . '/' . $hash;
        if (!is_dir($chunkDir)) {
            return ['code' => 1, 'message' => '分片文件不存在'];
        }
        
        // 检查分片是否都已上传
        for ($i = 0; $i < $chunkTotal; $i++) {
            if (!file_exists($chunkDir . '/' . $i)) {
                return ['code' => -1, 'message' => '分片 ' . $i . ' 不存在，请重新上传'];
            }
        }
        
        // 创建保存目录
        $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
        // $savePath = $config['file_path'] . '/' . date('Ymd');
        $configLogic = new ConfigLogic();
        $configVal = $configLogic->getConfig('DATA_IN');
        $savePath = $configVal['value'];
        if (!is_dir($savePath)) {
            mkdir($savePath, 0755, true);
        }
        
        // 合并文件
        // $filePath = $savePath . '/' . $hash . '.' . $fileExt;
        $filePath = $savePath . '/' . $fileName;
        $out = fopen($filePath, "wb");
        
        if ($out) {
            for ($i = 0; $i < $chunkTotal; $i++) {
                $chunkFile = $chunkDir . '/' . $i;
                $in = fopen($chunkFile, "rb");
                stream_copy_to_stream($in, $out);
                fclose($in);
            }
            fclose($out);
            
            // 清理分片文件
            for ($i = 0; $i < $chunkTotal; $i++) {
                @unlink($chunkDir . '/' . $i);
            }
            @rmdir($chunkDir);
            
            //$url = $savePath. '/' . $hash . '.' . $fileExt;
            $url = $savePath. '/' . $fileName;
            return [
                'code' => 0,
                'message' => '文件合并成功',
                'data' => [
                    'url' => $url,
                    'path' => $url,
                ]
            ];
        } else {
            return ['code' => -1, 'message' => '无法创建目标文件'];
        }
    }
}