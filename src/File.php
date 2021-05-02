<?php


namespace Sparrow;


class File
{
    /**
     * @var string $path
     */

    public string $path;

    /**
     * @var int|bool|mixed $fileSize
     */

    public $fileSize;

    /**
     * @var string $fileName
     */

    public string $fileName;

    /**
     * @var string $fileExtension
     */

    public string $fileExtension;

    /**
     * @var string $fileFullName
     */

    public string $fileFullName;

    /**
     * @var string $dirName
     */

    public string $dirName;

    /**
     * @var false|int|mixed $filePermission
     */

    public $filePermission;


    /**
     * File constructor.
     * @param string $path
     */

    public function __construct(string $path)
    {
        if (is_file($path)) {
            $this->path = $path;
            $file = pathinfo($path);
            $this->fileExtension = $file['extension'];
            $this->fileName = $file['filename'];
            $this->fileSize = filesize($path);
            $this->dirName = $file['dirname'];
            $this->fileFullName = $file['basename'];
            $this->filePermission = $this->permission();
        }
        else
            echo 'ERROR';
    }

    /**
     * creates file
     * @param string $path
     * @return false|resource
     */

    public static function create(string $path) {
        return fopen($path, 'w');
    }

    /**
     * writes into the file
     * @param string $data
     */

    public function write(string $data) {
        $file = fopen($this->path, 'w');
        fwrite($file, $data);
        fclose($file);
    }

    /**
     * opens file
     * @param string $mode
     * @return false|resource
     */

    public function open(string $mode) {
        return fopen($this->path, $mode);
    }

    /**
     * closes connection to the file
     * @param resource $file
     */

    public function close($file) {
        fclose($file);
    }

    /**
     * seeks file
     * @param $offset
     */

    public function seek($offset) {
        $file = $this->open('r');
        fseek($file, $offset);
        $this->close($file);
    }

    /**
     * truncates file
     * @param int $size
     */

    public function truncate(int $size) {
        $file = $this->open('a+');
        ftruncate($file, intval($size));
        $this->close($file);
    }

    /**
     * appends file
     * @param string $data
     */

    public function append(string $data) {
        $file = fopen($this->path, 'a+');
        fwrite($file, $data);
        fclose($file);
    }

    /**
     * removes file
     * Removes file
     */

    public function remove() {
        if (is_null($this->path))
            return;
        unlink($this->path);
    }

    /**
     * reads file
     * @return false|string
     */

    public function read() {
        $file = fopen($this->path, 'r');
        $data = @fread($file, filesize($this->path));
        fclose($file);
        return $data;
    }

    /**
     * moves file into the given path
     * @param string $path
     */

    public function move(string $path) {
        rename($this->path, $path);
    }

    /**
     * rename file
     * @param string $name
     */

    public function rename(string $name) {
        $this->move($name);
    }

    /**
     * copy file into the given path
     * @param string $path
     */

    public function copy(string $path) {
        copy($this->path, $path);
    }

    /**
     * returns file permissions
     * @return false|int
     */

    public function permission() {
        return fileperms($this->path);
    }

    /**
     * check if file exists in $path
     * @param $path
     * @return bool
     */

    public static function exists($path): bool
    {
        return file_exists($path);
    }

    /**
     * get contents of file in given path
     * @param string $path
     * @return false|string
     */

    public static function getContents(string $path)
    {
        return file_get_contents($path);
    }
}