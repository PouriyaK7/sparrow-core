<?php


namespace Sparrow;


class Session
{
    /**
     * @param string $key
     * @return mixed|null
     */

    public static function get(string $key)
    {
        return $_SESSION[$key] ?? null;
    }

    /**
     * @param mixed $key
     * @param mixed|null $value
     */

    public static function set($key, $value = null)
    {
        if (is_null($value))
            $_SESSION = $key;
        else
            $_SESSION[$key] = $value;
    }

    /**
     * @param string ...$key
     * @return bool
     */

    public static function exists(string ...$key): bool
    {
        foreach ($key as $item)
            if (!isset($_SESSION[$item]))
                return false;

        return true;
    }
}