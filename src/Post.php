<?php


namespace Sparrow;

/**
 * Class Posts
 * @package sparrow
 */

class Post
{

    /**
     * @param string $key
     * @param bool $trim
     * @param false $escaped
     * @return mixed|string
     */

    public static function get(string $key, $trim = false, $escaped = false) {
        if (!isset($_POST[$key]))
            return false;
        if (!$escaped)
            return ($trim? trim($_POST[$key]): $_POST[$key]);
        else
            return DB::connect()->escape_string($trim? trim($_POST[$key]): $_POST[$key]);
    }

    /**
     * @param $key
     * @param $value
     */

    public static function set($key, $value) {
        $_POST[$key] = $value;
    }

    /**
     * @return int
     */

    public static function count(): int
    {
        return count($_POST);
    }

    /**
     * @param string[] $key
     * @return bool
     */

    public static function exists(string ...$key): bool
    {
        foreach ($key as $item)
            if (!isset($_POST[$item]))
                return false;

        return true;
    }

    /**
     * @return array
     */

    public static function all(): array
    {
        return $_POST;
    }

    /**
     * @param array $array
     */

    public static function fill(array $array) {
        $_POST = $array;
    }

    /**
     * @return bool
     */

    public static function jsonToPost(): bool
    {
        $headers = getallheaders();
        if (isset($headers['Content_type']) && $headers['Content_type'] == 'application/json') {
            $json = file_get_contents('php://input');
            $data = json_decode($json, 1);
            self::fill(array_merge(Post::all(), $data));
            return true;
        }
        return  false;
    }
}