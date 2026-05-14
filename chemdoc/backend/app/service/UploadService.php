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
        $this->config = config('upload.') ?? [];
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
        $this->originalName = $file->getOriginalName();
        
        // 获取文件扩展名
        $ext = strtolower($file->getOriginalExtension());
        if (empty($ext)) {
            // 尝试从原始文件名获取扩展名
            $ext = strtolower(pathinfo($this->originalName, PATHINFO_EXTENSION));
        }
        
        // 根据类型获取允许的文件格式
        if ($type === 'images') {
            $allowedExt = $this->config['image_ext'] ?? ['jpg', 'jpeg', 'png', 'gif'];
        } else {
            $allowedExt = $this->config['file_ext'] ?? ['pdf', 'doc', 'docx', 'xls', 'xlsx'];
        }
        
        if (!in_array($ext, $allowedExt)) {
            return Result::error('不支持的文件格式：' . $ext);
        }

        $maxSize = $this->config['max_file_size'] ?? 10485760;
        $fileSize = $file->getSize();
        if ($fileSize === false || $fileSize > $maxSize) {
            return Result::error('文件大小超过限制或无法获取文件大小');
        }

        try {
            $path = $this->config['upload_path'] ?? 'uploads';
            $subDir = $this->getSubDir();
            
            $filename = $this->generateFilename($ext);
            
            $fullDir = './' . $path . '/' . $type . '/' . $subDir;
            $savePath = $path . '/' . $type . '/' . $subDir . '/' . $filename;
            
            if (!is_dir($fullDir)) {
                mkdir($fullDir, 0755, true);
            }
            
            $info = $file->move($fullDir, $filename);
            
            if (!$info) {
                return Result::error('文件移动失败');
            }
            
            $url = '/' . $savePath;
            
            return Result::success([
                'url' => $url,
                'path' => $savePath,
                'filename' => $filename,
                'original_name' => $this->originalName,
                'size' => $fileSize,
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
        return $this->originalName;
    }
}
