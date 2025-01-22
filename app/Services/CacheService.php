<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Exception;

class CacheService
{
    /**
     * 获取缓存
     */
    public function get(string $key, $default = null)
    {
        return Cache::get($key, $default);
    }

    /**
     * 设置缓存
     */
    public function set(string $key, $value, $ttl = null): bool
    {
        return Cache::put($key, $value, $ttl);
    }

    /**
     * 删除缓存
     */
    public function delete(string $key): bool
    {
        return Cache::forget($key);
    }

    /**
     * 清除所有缓存
     */
    public function clear(): bool
    {
        return Cache::flush();
    }

    /**
     * 获取或设置缓存
     */
    public function remember(string $key, $ttl, \Closure $callback)
    {
        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * 永久缓存
     */
    public function forever(string $key, $value): bool
    {
        return Cache::forever($key, $value);
    }

    /**
     * 递增
     */
    public function increment(string $key, $amount = 1)
    {
        return Cache::increment($key, $amount);
    }

    /**
     * 递减
     */
    public function decrement(string $key, $amount = 1)
    {
        return Cache::decrement($key, $amount);
    }

    /**
     * 获取系统配置缓存
     */
    public function getConfig(string $key, $default = null)
    {
        return $this->remember('config:' . $key, 3600, function () use ($key, $default) {
            return \DB::table('system_configs')->where('key', $key)->value('value') ?? $default;
        });
    }

    /**
     * 设置系统配置缓存
     */
    public function setConfig(string $key, $value): void
    {
        $this->set('config:' . $key, $value, 3600);
    }

    /**
     * 清除系统配置缓存
     */
    public function clearConfig(string $key = null): void
    {
        if ($key) {
            $this->delete('config:' . $key);
        } else {
            $this->clearPattern('config:*');
        }
    }

    /**
     * 获取用户数据缓存
     */
    public function getUserData(int $userId, string $key, $default = null)
    {
        return $this->get("user:{$userId}:{$key}", $default);
    }

    /**
     * 设置用户数据缓存
     */
    public function setUserData(int $userId, string $key, $value, $ttl = null): void
    {
        $this->set("user:{$userId}:{$key}", $value, $ttl);
    }

    /**
     * 清除用户数据缓存
     */
    public function clearUserData(int $userId, string $key = null): void
    {
        if ($key) {
            $this->delete("user:{$userId}:{$key}");
        } else {
            $this->clearPattern("user:{$userId}:*");
        }
    }

    /**
     * 获取代理商数据缓存
     */
    public function getAgentData(int $agentId, string $key, $default = null)
    {
        return $this->get("agent:{$agentId}:{$key}", $default);
    }

    /**
     * 设置代理商数据缓存
     */
    public function setAgentData(int $agentId, string $key, $value, $ttl = null): void
    {
        $this->set("agent:{$agentId}:{$key}", $value, $ttl);
    }

    /**
     * 清除代理商数据缓存
     */
    public function clearAgentData(int $agentId, string $key = null): void
    {
        if ($key) {
            $this->delete("agent:{$agentId}:{$key}");
        } else {
            $this->clearPattern("agent:{$agentId}:*");
        }
    }

    /**
     * 获取查询数据缓存
     */
    public function getQueryData(int $queryId, string $key, $default = null)
    {
        return $this->get("query:{$queryId}:{$key}", $default);
    }

    /**
     * 设置查询数据缓存
     */
    public function setQueryData(int $queryId, string $key, $value, $ttl = null): void
    {
        $this->set("query:{$queryId}:{$key}", $value, $ttl);
    }

    /**
     * 清除查询数据缓存
     */
    public function clearQueryData(int $queryId, string $key = null): void
    {
        if ($key) {
            $this->delete("query:{$queryId}:{$key}");
        } else {
            $this->clearPattern("query:{$queryId}:*");
        }
    }

    /**
     * 清除匹配模式的缓存
     */
    protected function clearPattern(string $pattern): void
    {
        if (config('cache.default') === 'redis') {
            $redis = Redis::connection();
            $keys = $redis->keys($pattern);
            if (!empty($keys)) {
                $redis->del($keys);
            }
        } else {
            Cache::flush(); // 其他驱动只能清除所有缓存
        }
    }
} 