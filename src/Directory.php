<?php


namespace Sparrow;


use DirectoryIterator;

class Directory
{

    /**
     * @var string $path path to the file
     */

    public string $path;

    /**
     * @var string $name The name of the file
     */

    public string $name;

    /**
     * @var false|int|mixed $permission The permission of the file
     */

    public  $permission;

    /**
     * @var string $rootPath the path of the root directory
     */

    public string $rootPath = '/var/www/';

    /**
     * Directory constructor.
     * @param string $path
     */

    public function __construct(string $path)
    {
        $this->path = $path;
        if (is_file($this->path)) {
            $this->permission = $this->permission();
            $this->name = dirname($this->path);
        }
    }

    /**
     * @param string $path
     * @return bool
     */

    public static function create(string $path): bool
    {
        if (self::isDir($path))
            return true;
        return mkdir($path);
    }

    /**
     * @param string $path
     * @return bool
     */

    public static function isDir(string $path): bool
    {
        return is_dir($path);
    }

    /**
     * @param null|string $dir
     * @return array
     */

    public function list(string $dir = null): array
    {
        return scandir($dir ?? $this->path);
    }

    /**
     * @return false|resource
     */

    public function openDir()
    {
        return opendir($this->path);
    }

    /**
     * Removes the directory
     */

    public function remove()
    {
        if (self::isDir($this->path))
            rmdir($this->path);
    }

    /**
     * @param string $name
     * renames the directory
     */

    public function rename(string $name)
    {
        $this->name = rename($this->path, $name) ? $name : $this->name;
    }

    /**
     * Returns the directory permissions
     * @return false|int
     */

    public function permission()
    {
        return fileperms($this->path);
    }

    /**
     * Checks if the directory is writable or not
     * @return bool
     */

    public function isWritable(): bool
    {
        return is_writable($this->path);
    }

    /**
     * Serializes the data
     * @param $data
     * @return string
     */

    public function serialize($data): string
    {
        return serialize($data);
    }

    /**
     * Un serializes the data
     * @param $data
     * @return mixed
     */

    public function toString($data)
    {
        return unserialize($data);
    }

    /**
     * @param $dir
     * @param false $ignoreEmpty
     * @return array|null
     */

    public function tree($dir, $ignoreEmpty = false): ?array
    {
        if (!$dir instanceof DirectoryIterator) {
            $dir = new DirectoryIterator((string)$dir);
        }
        $dirs = [];
        foreach ($dir as $node) {
            if ($node->isDir() && !$node->isDot()) {
                $tree = $this->tree($node->getPathname(), $ignoreEmpty);
                if (!$ignoreEmpty || count($tree)) {
                    $dirs[$node->getFilename()] = $tree;
                }
            }
        }
        asort($dirs);

        return $dirs != [] ? $dirs : null;
    }

    public static function rootPath(): string
    {
        return dirname(__DIR__);
    }
}