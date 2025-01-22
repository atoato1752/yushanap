<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class FileService
{
    /**
     * 上传文件
     */
    public function upload(UploadedFile $file, string $path = '', array $options = []): string
    {
        try {
            // 生成文件名
            $filename = $this->generateFilename($file);
            
            // 获取存储磁盘
            $disk = $options['disk'] ?? 'public';
            
            // 存储路径
            $storagePath = trim($path, '/') . '/' . $filename;
            
            // 上传文件
            $path = Storage::disk($disk)->putFileAs(
                $path,
                $file,
                $filename,
                $options['visibility'] ?? 'public'
            );

            // 返回文件访问路径
            return Storage::disk($disk)->url($path);
        } catch (Exception $e) {
            \Log::error('文件上传失败', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName()
            ]);
            throw new Exception('文件上传失败，请稍后重试');
        }
    }

    /**
     * 上传图片
     */
    public function uploadImage(UploadedFile $file, array $options = []): string
    {
        // 验证图片
        $this->validateImage($file);

        // 处理图片
        if (!empty($options['process'])) {
            $file = $this->processImage($file, $options['process']);
        }

        return $this->upload($file, 'images', $options);
    }

    /**
     * 上传多个文件
     */
    public function uploadMultiple(array $files, string $path = '', array $options = []): array
    {
        $paths = [];
        foreach ($files as $file) {
            $paths[] = $this->upload($file, $path, $options);
        }
        return $paths;
    }

    /**
     * 删除文件
     */
    public function delete(string $path, string $disk = 'public'): bool
    {
        try {
            return Storage::disk($disk)->delete($path);
        } catch (Exception $e) {
            \Log::error('文件删除失败', [
                'error' => $e->getMessage(),
                'path' => $path
            ]);
            return false;
        }
    }

    /**
     * 生成文件名
     */
    protected function generateFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        return date('Ymd') . '/' . Str::random(32) . '.' . $extension;
    }

    /**
     * 验证图片
     */
    protected function validateImage(UploadedFile $file): void
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        
        if (!in_array($file->getMimeType(), $allowedTypes)) {
            throw new Exception('不支持的图片格式');
        }

        $maxSize = config('filesystems.max_image_size', 5120); // 默认5MB
        if ($file->getSize() > $maxSize * 1024) {
            throw new Exception('图片大小不能超过' . ($maxSize / 1024) . 'MB');
        }
    }

    /**
     * 处理图片
     */
    protected function processImage(UploadedFile $file, array $options): UploadedFile
    {
        // 创建图片实例
        $image = \Image::make($file);

        // 调整尺寸
        if (!empty($options['resize'])) {
            $width = $options['resize']['width'] ?? null;
            $height = $options['resize']['height'] ?? null;
            $image->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        // 裁剪
        if (!empty($options['crop'])) {
            $width = $options['crop']['width'];
            $height = $options['crop']['height'];
            $x = $options['crop']['x'] ?? 0;
            $y = $options['crop']['y'] ?? 0;
            $image->crop($width, $height, $x, $y);
        }

        // 压缩质量
        $quality = $options['quality'] ?? 90;
        $image->save(null, $quality);

        return new UploadedFile(
            $image->basePath(),
            $file->getClientOriginalName(),
            $file->getMimeType(),
            null,
            true
        );
    }

    /**
     * 获取文件信息
     */
    public function getFileInfo(string $path, string $disk = 'public'): array
    {
        $storage = Storage::disk($disk);
        
        if (!$storage->exists($path)) {
            throw new Exception('文件不存在');
        }

        return [
            'name' => basename($path),
            'path' => $path,
            'url' => $storage->url($path),
            'size' => $storage->size($path),
            'mime_type' => $storage->mimeType($path),
            'last_modified' => $storage->lastModified($path)
        ];
    }

    /**
     * 检查文件是否存在
     */
    public function exists(string $path, string $disk = 'public'): bool
    {
        return Storage::disk($disk)->exists($path);
    }

    /**
     * 获取文件内容
     */
    public function get(string $path, string $disk = 'public'): string
    {
        if (!$this->exists($path, $disk)) {
            throw new Exception('文件不存在');
        }

        return Storage::disk($disk)->get($path);
    }

    /**
     * 获取文件访问URL
     */
    public function url(string $path, string $disk = 'public'): string
    {
        if (!$this->exists($path, $disk)) {
            throw new Exception('文件不存在');
        }

        return Storage::disk($disk)->url($path);
    }
} 