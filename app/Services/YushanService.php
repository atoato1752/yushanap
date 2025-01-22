<?php

namespace App\Services;

use App\Models\Query;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Exception;

class YushanService
{
    protected $config;
    protected $apiService;
    protected $logService;

    public function __construct(ApiService $apiService, LogService $logService)
    {
        $this->config = config('yushan');
        $this->apiService = $apiService;
        $this->logService = $logService;
    }

    /**
     * 发起信用查询
     */
    public function query(Query $query): array
    {
        try {
            // 发送查询请求
            $response = $this->apiService->post('credit/query', [
                'name' => $query->name,
                'id_card' => $query->id_card,
                'query_id' => $query->id,
                'callback_url' => route('api.yushan.callback')
            ]);

            // 更新查询状态
            $query->update([
                'status' => 'processing',
                'yushan_request_id' => $response['request_id']
            ]);

            // 记录日志
            $this->logService->query('submit', [
                'query_id' => $query->id,
                'request_id' => $response['request_id']
            ]);

            return $response;
        } catch (Exception $e) {
            // 更新查询状态
            $query->update(['status' => 'failed']);

            // 记录错误日志
            $this->logService->error($e, [
                'query_id' => $query->id,
                'name' => $query->name,
                'id_card' => $query->id_card
            ]);

            throw new Exception('信用查询请求失败，请稍后重试');
        }
    }

    /**
     * 处理回调通知
     */
    public function handleCallback(array $data): void
    {
        // 验证签名
        if (!$this->verifySignature($data)) {
            throw new Exception('回调签名验证失败');
        }

        $query = Query::where('yushan_request_id', $data['request_id'])->first();
        if (!$query) {
            throw new Exception('未找到对应的查询记录');
        }

        try {
            if ($data['status'] === 'success') {
                // 更新查询状态和结果
                $query->update([
                    'status' => 'completed',
                    'result' => $data['result']
                ]);

                // 发送通知
                event(new QueryCompleted($query));
            } else {
                // 更新查询状态
                $query->update([
                    'status' => 'failed',
                    'error_message' => $data['message']
                ]);
            }

            // 记录日志
            $this->logService->query('callback', [
                'query_id' => $query->id,
                'request_id' => $data['request_id'],
                'status' => $data['status']
            ]);
        } catch (Exception $e) {
            $this->logService->error($e, [
                'query_id' => $query->id,
                'callback_data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * 获取查询结果
     */
    public function getQueryResult(Query $query): array
    {
        if ($query->status !== 'completed') {
            throw new Exception('查询尚未完成');
        }

        try {
            return $this->apiService->get('credit/result/' . $query->yushan_request_id);
        } catch (Exception $e) {
            $this->logService->error($e, [
                'query_id' => $query->id,
                'request_id' => $query->yushan_request_id
            ]);
            throw new Exception('获取查询结果失败，请稍后重试');
        }
    }

    /**
     * 获取查询进度
     */
    public function getQueryProgress(Query $query): array
    {
        try {
            return $this->apiService->get('credit/progress/' . $query->yushan_request_id);
        } catch (Exception $e) {
            $this->logService->error($e, [
                'query_id' => $query->id,
                'request_id' => $query->yushan_request_id
            ]);
            throw new Exception('获取查询进度失败，请稍后重试');
        }
    }

    /**
     * 验证回调签名
     */
    protected function verifySignature(array $data): bool
    {
        $signature = $data['signature'];
        unset($data['signature']);

        // 按键名排序
        ksort($data);

        // 构建签名字符串
        $string = '';
        foreach ($data as $key => $value) {
            $string .= $key . '=' . $value . '&';
        }
        $string .= 'key=' . $this->config['api_key'];

        // 验证签名
        return $signature === md5($string);
    }

    /**
     * 生成签名
     */
    protected function generateSignature(array $data): string
    {
        // 按键名排序
        ksort($data);

        // 构建签名字符串
        $string = '';
        foreach ($data as $key => $value) {
            $string .= $key . '=' . $value . '&';
        }
        $string .= 'key=' . $this->config['api_key'];

        return md5($string);
    }

    /**
     * 检查API状态
     */
    public function checkApiStatus(): bool
    {
        try {
            $response = $this->apiService->get('system/status');
            return $response['status'] === 'normal';
        } catch (Exception $e) {
            $this->logService->error($e);
            return false;
        }
    }
} 