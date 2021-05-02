<?php
namespace Sparrow;

class Redis {
    /**
     * @var \Redis|null|mixed $redis
     */
    private static $redis;

    /**
     * @return \Redis Returns Redis object
     */

    public static function Redis() : \Redis
    {
        if (isset(self::$redis)) {
            return self::$redis;
        }
        self::$redis = new \Redis();
        self::$redis->connect(REDIS_HOST);
        self::$redis->auth(REDIS_PASS);
        self::$redis->setOption(\Redis::OPT_SCAN, \Redis::SCAN_RETRY);
        return self::$redis;
    }

    /**
     * @param string $key
     * @param mixed $value
     * Stores given value as json in redis
     */

    public static function set(string $key, $value)
    {
        self::Redis()->set($key, json_encode($value));
    }

    /**
     * @param string $key
     * @return mixed
     * Returns the value of the key given to it from redis
     */

    public static function get(string $key) {
        return json_decode(self::Redis()->get($key), 1)??null;
    }

    public static function end()
    {
        self::$redis = null;
    }
}