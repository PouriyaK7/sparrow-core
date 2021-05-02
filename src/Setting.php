<?php


namespace Sparrow;


class Setting
{

    /**
     * @var mixed $settingsTTL
     */

    public static $settingsTTL;

    /**
     * @var array $settings
     */

    public static array $settings;

    /**
     * @param string $name
     * @return mixed
     */

    public static function get(string $name)
    {
        if (!self::$settingsTTL || self::$settingsTTL < time()) {
            $settings = DB::connect()->query('SELECT * FROM `settings`')->fetch_all(MYSQLI_ASSOC);
            foreach ($settings as $setting)
                Redis::Redis()->hSet('site_settings', $setting['name'], $setting['value']);
            self::$settings = Redis::Redis()->hGetAll('site_settings');
            self::$settingsTTL = time() + 30;
        }
        return self::$settings[$name] ?? 'ERROR';
    }

    /**
     * @param mixed $name
     * @param null|mixed $value
     * @return bool
     */

    public static function set($name, $value = null): bool
    {
        $settings = Redis::Redis()->hGetAll('site_settings');
        if (!is_null($value)) {
            $value = htmlentities($value);
            DB::prepare('DELETE FROM settings WHERE `name` = ?', null, 3, 's', $name);
            DB::prepare('INSERT INTO `settings`(`name`, `value`) VALUES (?,?)', null, 3, 'ss', $name, $value);
            $settings[$name] = $value;
            Redis::Redis()->hSet('site_settings', $name, $value);
        } elseif (is_array($name)) {
            foreach ($name as $index => $item) {
                $item = htmlentities($item);
                DB::prepare('DELETE FROM `settings` WHERE `name` = ?', null, 3, 's', $index);
                DB::prepare('INSERT INTO `settings`(`name`, `value`) VALUES (?,?)', null, 3, 'ss', $index, $item);
                $settings[$index] = $item;
                Redis::Redis()->hSet('site_settings', $index, $item);
            }
        }
        if (self::$settings = (array)$settings)
            return true;
        else
            return false;
    }

    /**
     * @param string $settingName
     * @param string $path
     * @return string
     */

    public static function protoSet(string $settingName, $path = ''): string
    {
        return 'http' . ($_SERVER['SERVER_PORT'] == 443 || self::get('force_ssl') ? 's' : '') . '://' . self::get($settingName) . ($path ? '/' . $path : '');
    }
}