<?php

namespace App\Cache\Repository;


use Illuminate\Container\Container;
use Illuminate\Support\Facades\Cache;
use Swoole\Coroutine\Redis;

abstract class AbstractRedis
{
    protected $prefix = 'rds';

    protected $name = '';
    /**
     * 静态实例对象
     * @param array $var 实例参数
     * @param boolean $new 创建新实例
     * @return static|mixed
     */
    public static function getInstance()
    {
        return Container::getInstance()->make(static::class);
    }

    /**
     * 获取 Redis 连接
     *
     * @return Redis|mixed
     */
    protected function redis()
    {
       // return Cache::store('redis');
        return  app('redis.connection');

    }

    /**
     * 获取缓存 KEY
     *
     * @param string|array $key
     * @return string
     */
    protected function getCacheKey($key = '')
    {
        $params = [$this->prefix, $this->name];
        if (is_array($key)) {
            $params = array_merge($params, $key);
        } else {
            $params[] = $key;
        }

        return $this->filter($params);
    }

    protected function filter(array $params = [])
    {

        foreach ($params as $k => $param) {
            $params[$k] = trim($param, ':');
        }
        return implode(':', array_filter($params));
    }
}
