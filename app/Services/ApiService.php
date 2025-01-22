<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Exception;

class ApiService
{
    protected $config;
    protected $logService;

    public function __construct(LogService $logService)
    {
        $this->config = config('api');
        $this->logService = $logService;
    }

    /**
     * 发送API请求
     */
    public function request(string $method, string $endpoint, array $data = [], array $headers = []): array
    {
        try {
            // 构建请求URL
            $url = rtrim($this->config['base_url'], '/') . '/' . ltrim($endpoint, '/');

            // 合并默认头部
            $headers = array_merge([
                'Authorization' => 'Bearer ' . $this->getAccessToken(),
                'Accept' => 'application/json',
            ], $headers);

            // 发送请求
            $response = Http::withHeaders($headers)
                ->timeout($this->config['timeout'])
                ->$method($url, $data);

            // 检查响应状态
            if (!$response->successful()) {
                throw new Exception('API请求失败：' . $response->body());
            }

            // 解析响应
            $result = $response->json();
            if (!$result['success']) {
                throw new Exception('API业务错误：' . $result['message']);
            }

            // 记录日志
            $this->logService->operation('api', 'request', [
                'method' => $method,
                'endpoint' => $endpoint,
                'data' => $data,
                'response' => $result
            ]);

            return $result['data'];
        } catch (Exception $e) {
            $this->logService->error($e, [
                'method' => $method,
                'endpoint' => $endpoint,
                'data' => $data
            ]);
            throw new Exception('API请求失败，请稍后重试');
        }
    }

    /**
     * 获取访问令牌
     */
    protected function getAccessToken(): string
    {
        return Cache::remember('api:access_token', 3600, function () {
            $response = Http::post($this->config['auth_url'], [
                'app_id' => $this->config['app_id'],
                'app_secret' => $this->config['app_secret']
            ]);

            if (!$response->successful()) {
                throw new Exception('获取访问令牌失败：' . $response->body());
            }

            $result = $response->json();
            if (!$result['success']) {
                throw new Exception('获取访问令牌失败：' . $result['message']);
            }

            return $result['data']['access_token'];
        });
    }

    /**
     * 刷新访问令牌
     */
    public function refreshToken(): void
    {
        Cache::forget('api:access_token');
        $this->getAccessToken();
    }

    /**
     * GET请求
     */
    public function get(string $endpoint, array $params = [], array $headers = []): array
    {
        return $this->request('get', $endpoint, $params, $headers);
    }

    /**
     * POST请求
     */
    public function post(string $endpoint, array $data = [], array $headers = []): array
    {
        return $this->request('post', $endpoint, $data, $headers);
    }

    /**
     * PUT请求
     */
    public function put(string $endpoint, array $data = [], array $headers = []): array
    {
        return $this->request('put', $endpoint, $data, $headers);
    }

    /**
     * DELETE请求
     */
    public function delete(string $endpoint, array $data = [], array $headers = []): array
    {
        return $this->request('delete', $endpoint, $data, $headers);
    }

    /**
     * 上传文件
     */
    public function upload(string $endpoint, string $filePath, array $data = [], array $headers = []): array
    {
        try {
            $url = rtrim($this->config['base_url'], '/') . '/' . ltrim($endpoint, '/');

            $response = Http::withHeaders(array_merge([
                'Authorization' => 'Bearer ' . $this->getAccessToken(),
                'Accept' => 'application/json',
            ], $headers))
            ->timeout($this->config['upload_timeout'])
            ->attach('file', file_get_contents($filePath), basename($filePath))
            ->post($url, $data);

            if (!$response->successful()) {
                throw new Exception('文件上传失败：' . $response->body());
            }

            $result = $response->json();
            if (!$result['success']) {
                throw new Exception('文件上传失败：' . $result['message']);
            }

            return $result['data'];
        } catch (Exception $e) {
            $this->logService->error($e, [
                'endpoint' => $endpoint,
                'file' => $filePath,
                'data' => $data
            ]);
            throw new Exception('文件上传失败，请稍后重试');
        }
    }

    /**
     * 下载文件
     */
    public function download(string $endpoint, string $savePath, array $params = [], array $headers = []): string
    {
        try {
            $url = rtrim($this->config['base_url'], '/') . '/' . ltrim($endpoint, '/');

            $response = Http::withHeaders(array_merge([
                'Authorization' => 'Bearer ' . $this->getAccessToken(),
            ], $headers))
            ->timeout($this->config['download_timeout'])
            ->get($url, $params);

            if (!$response->successful()) {
                throw new Exception('文件下载失败：' . $response->body());
            }

            file_put_contents($savePath, $response->body());

            return $savePath;
        } catch (Exception $e) {
            $this->logService->error($e, [
                'endpoint' => $endpoint,
                'save_path' => $savePath,
                'params' => $params
            ]);
            throw new Exception('文件下载失败，请稍后重试');
        }
    }
} 