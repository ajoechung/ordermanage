<?php
namespace app\service;

use think\facade\Db;
use think\facade\Filesystem;
use think\file\UploadedFile;

class UploadService
{
    protected array $config;
    protected string $disk = 'public';
    protected ?string $originalName = null;

    public function __construct()
    {
        $this->config = config('upload.');
    }

    public function uploadImage(UploadedFile $file): array
    {
        return $this->upload($file, 'images');
    }

    public function uploadFile(UploadedFile $file): array
    {
        return $this->upload($file, 'files');
    }

    public function upload(UploadedFile $file, string $type = 'images'): array
    {
        $ext = strtolower($file->getOriginalExtension());
        $this->originalName = $file->getOriginalName();
        
        $allowedExt = $this->config['allowed_ext'] ?? [];
        if (!in_array($ext, $allowedExt)) {
            return Result::error('不支持的文件格式');
        }

        $maxSize = $this->config['max_file_size'] ?? 10485760;
        if ($file->getSize() > $maxSize) {
            return Result::error('文件大小超过限制');
        }

        try {
            $path = $this->config['upload_path'] ?? 'uploads';
            $subDir = $this->getSubDir();
            
            $filename = $this->generateFilename($ext);
            
            $fullPath = $path . '/' . $type . '/' . $subDir;
            $savePath = $fullPath . '/' . $filename;
            
            $file->move('.' . $savePath);
            
            $url = '/' . $savePath;
            
            return Result::success([
                'url' => $url,
                'path' => $savePath,
                'filename' => $filename,
                'original_name' => $this->originalName,
                'size' => $file->getSize(),
                'ext' => $ext,
            ], '上传成功');

        } catch (\Exception $e) {
            return Result::error('上传失败：' . $e->getMessage());
        }
    }

    public function delete(string $path): array
    {
        $fullPath = '.' . $path;
        
        if (!file_exists($fullPath)) {
            return Result::error('文件不存在');
        }

        if (!unlink($fullPath)) {
            return Result::error('删除失败');
        }

        return Result::success(null, '删除成功');
    }

    protected function getSubDir(): string
    {
        return date('Y') . '/' . date('m') . '/' . date('d');
    }

    protected function generateFilename(string $ext): string
    {
        return $this->originalName . '.' . $ext;
    }
}
