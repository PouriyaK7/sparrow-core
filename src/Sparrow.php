<?php


namespace Sparrow;

function dd(...$args)
{
    var_dump(...$args);
    exit();
}

require '../vendor/autoload.php';


final class Sparrow
{
    /**
     * @var array $error
     */

    public static array $error = [];

    /**
     * @var array $infos
     */

    public static array $infos = [];

    /**
     * @var string[] $SetStmt
     */

    private static array $SetStmt = ['INSERT INTO `settings` VALUES (?, ?)',
        'UPDATE `settings` SET `value` = ? WHERE `name` = ?'];

    /**
     * @param string $page
     * @param false $permanent
     */

    public static function redirect(string $page, $permanent = false)
    {
        if (headers_sent())
            echo '<script type="text/javascript"> window.location = "' . $page . '"; </script>';
        else {
            ob_clean();
            header('Location:' . $page, !!$permanent, $permanent ? 301 : 302);
        }
        exit;
    }

    /**
     * @param string $name
     * @param false $val
     * @return false|mixed|null
     */

    public static function stats(string $name, $val = false)
    {
        if (Redis::get('site_statistics') === false) {
            $statistics = DB::prepare('SELECT * FROM `statistics`');
            Redis::set('site_statistics', $statistics);
        }
        $set = Redis::get('site_statistics');
        if (!isset($val))
            return $set;
        if ($set === false)
            $setNum = 0;
        else
            $setNum = 1;
        if (is_string(self::$SetStmt[0]))
            foreach (self::$SetStmt as $index => &$stmt)
                if ($index == $setNum)
                    DB::prepare($stmt, null, 1, 'ss', $name, $val);
        Redis::set('site_statistics', [$name, $val]);
        return $val;
    }

    /**
     * sets statistics
     */

    public static function statsVisit() {
        DB::prepare('UPDATE statistics SET `value` = `value` + 1 WHERE name IN("visit_today", "visit_month") ', null, 1);
        if (Redis::get('site_statistics') === false){
            $statistics = DB::prepare('SELECT * FROM statistics');
            Redis::set('site_statistics', $statistics);
        }
    }

    /**
     * @param int $length
     * @return false|string
     * @throws \Exception
     */

    public static function randomString(int $length) {
        $chars = "0123456789_ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        $Password = '';
        for($i = 0; $i < $length; $i += 64){
            $rndString = random_bytes(30) . time();
            $Password .= hash('sha256', $rndString);
        }
        $CP = floor($length / random_int(2, floor($length / 5)));
        $CC = strlen($chars);
        for($i = 0; $i < $CP; $i++)
            $Password[random_int(0, $length - 1)] = $chars[random_int(0, $CC - 1)];
        $Password = substr($Password, 0, $length);
        return $Password;
    }

    /**
     * @param string $url
     */

    public static function goTo(string $url)
    {
        if (headers_sent()) {
            echo '<script type="text/javascript"> window.location = "' . $url . '"; </script>';
        }
        else {
            ob_clean();
            header('location: ' . $url);
        }
    }

    /**
     * @param array|null $errors
     * @return array
     */

    public static function errors(array $errors = null): ?array
    {
        if (isset($errors))
            self::$error = array_merge(self::$error ?? [], $errors);
        else
            return self::$error? self::$error: null;
    }

    /**
     * @param array|null $infos
     * @return array
     */

    public static function infos(array $infos = null): ?array
    {
        if (isset($infos))
            self::$infos = array_merge(self::$infos, $infos);
        else
            return self::$infos?: null;
    }

    /**
     * Unsets all errors and infos stored in session
     */

    public static function resetMessages() {
        self::$infos = [];
        self::$error = [];
    }

    /**
     * @return bool
     */

    public static function isError(): bool
    {
        if (!isset($_SESSION['errors']))
            return true;
        elseif ($_SESSION['errors'] == [])
            return true;
        return false;
    }

    /**
     * @param string $string
     * @param string $key
     * @return string
     */

    static public function encode(string $string, string $key): string
    {
        $key = sha1($key);
        $hash = '';
        for($i = 0, $j = 0; $i < strlen($string); $i++, $j++){
            $ord = ord(substr($string, $i, 1)) + ord(substr($key, $j, 1));
            if($j == strlen($key))
                $j = 0;
            $hash .= strrev(base_convert($ord, 10, 36));
        }
        return $hash;
    }

    /**
     * @param string $string
     * @param string $key
     * @return string
     */

    static public function decode(string $string, string $key): string
    {
        $key = sha1($key);
        $hash = '';
        for($i = 0, $j = 0; $i < strlen($string); $i += 2, $j++){
            $ordStr = base_convert(strrev(substr($string, $i, 2)), 36, 10);
            if($j == strlen($key))
                $j = 0;
            $ordKey = ord(substr($key, $j, 1));
            $hash .= chr($ordStr - $ordKey);
        }
        return $hash;
    }

    public static function csvRecord($row): string
    {
        foreach($row as $item){
            $item = preg_replace("/\t/", "\\t", $item);
            $item = preg_replace("/\r?\n/", "\\n", $item);
            if(strstr($item, '"'))
                $item = '"' . str_replace('"', '""', $item) . '"';
            $record[] = $item;
        }
        return implode("؛", $row) . PHP_EOL;
    }

    public static function checkUserToken($token)
    {
        return DB::prepare(
            'SELECT * FROM `user_tokens` WHERE `token` = ?',
            null,
            DB::FETCH_ALL,
            's',
            $token
        );
    }
}