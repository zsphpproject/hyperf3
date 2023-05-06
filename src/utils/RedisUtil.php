<?php

namespace Zsgogo\utils;

use Hyperf\Redis\Redis;
use Hyperf\Context\ApplicationContext;


class RedisUtil {

    /**
     * @var Redis|mixed $redis
     */
    protected Redis $redis;

    public function __construct() {
        $container = ApplicationContext::getContainer();
        $this->redis = $container->get(Redis::class);
    }


    /**
     * key,value操作
     * @param string $key
     * @return array
     * @throws \RedisException
     */
    public function keys(string $key = "*"): array {
        return $this->redis->keys($key);
    }

    public function set(string $key,string $value,int $ttl): void {
        $this->redis->set($key,$value,$ttl);
    }

    public function get(string $key){
        return $this->redis->get($key);
    }

    public function delete(string|array $key): int {
        return $this->redis->del($key);
    }


    /**
     * list操作
     * @param string $key
     * @param string $value
     * @return mixed
     * @throws \RedisException
     */
    public function lpush(string $key,string $value): mixed {
        return $this->redis->lpush($key,$value);
    }

    public function rpush(string $key,string $value): bool|int {
        return $this->redis->rpush($key,$value);
    }

    public function lpop(string $key): mixed {
        return $this->redis->lpop($key);
    }

    public function rpop(string $key): mixed {
        return $this->redis->rpop($key);
    }

    public function lrange(string $key,int $start = 0,int $end = -1): array {
        return $this->redis->lrange($key,$start,$end);
    }

    public function llen(string $key) :int {
        return $this->redis->llen($key);
    }

    public function expire(string $key,int $ttl): bool {
        return $this->redis->expire($key,$ttl);
    }


    /**
     * zset操作
     * @param string $key
     * @param string $value
     * @return mixed
     * @throws \RedisException
     */
    public function sadd(string $key,string $value): mixed {
        return $this->redis->sadd($key,$value);
    }

    public function smembers(string $key) :array {
        return $this->redis->smembers($key);
    }

    public function scard(string $key): int {
        return $this->redis->scard($key);
    }

    public function srem(string $key,string $value): int {
        return $this->redis->srem($key,$value);
    }

    public function sismember(string $key,string $value) :bool {
        return $this->redis->sismember($key,$value);
    }
}