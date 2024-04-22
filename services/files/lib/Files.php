<?php
namespace services\files\lib;

use services\files\exceptions\ECantCreatePath;
use services\files\exceptions\EConfigError;

class Files {

    private static array $files_config;

    public static function loadConfig (): array 
    {
        if (!empty(self::$files_config)) {
            return self::$files_config;
        }

        self::$files_config = require __DIR__ . '/../../../config/files.php';

        if (!isset(self::$files_config['quota'])) {
            throw new EConfigError("files.json", "quota");
        }

        if (!isset(self::$files_config['dir'])) {
            throw new EConfigError("files.json", "dir");
        }

        if (!isset(self::$files_config['salt'])) {
            throw new EConfigError("files.json", "dir");
        }

        return self::$files_config;
    }

    public static function fileID (string $filename)
    {
        return sha1($filename . self::loadConfig ()['salt']);
    }

    public static function translateQuota (string $quota): int 
    {
        $quota_size = (int) $quota;

        if (0 === $quota_size) {
            throw new \Exception("Quota size can not be zero");
        }

        if (strpos($quota, 'kb')) {
            $quota_size = 1024 * $quota_size;
        } elseif (strpos($quota, 'mb')) {
            $quota_size = 1048576 * $quota_size;
        } elseif (strpos($quota, 'gb')) {
            $quota_size = 1073741824 * $quota_size;
        }

        return $quota_size;
    }

    public static function quota (string $username)
    {
        $files_config = Files::loadConfig();

        $quota = strtolower($files_config['quota']);

        $total = Files::translateQuota($quota);
        $used = Files::calcSpace($username);
        $free = $total - $used;

        return ['quota' => [
            'total' => $total,
            'used' => $used,
            'free' => $free
        ]];
    }

    public static function checkStoragePath () 
    {
        $files_config = self::loadConfig();
        $dir = $files_config['dir'];

        if (DIRECTORY_SEPARATOR !== substr($dir, 0, 1)) {
            $dir = __DIR__ . '/../' . $dir;
        }

        if (!file_exists($dir)) {
            if (!mkdir($dir)) {
                throw new ECantCreatePath($dir);
            }
        }
    }

    public static function checkUserPath (string $username): string 
    {
        $files_config = self::loadConfig();
        $dir = $files_config['dir'];

        if (DIRECTORY_SEPARATOR !== substr($dir, 0, 1)) {
            $dir = __DIR__ . '/../' . $dir;
        }

        $dir = $dir . '/' . $username;

        if (!file_exists($dir)) {
            if (!mkdir($dir)) {
                throw new ECantCreatePath($dir);
            }
        }

        return $dir;
    }

    public static function calcSpace (string $username): int 
    {
        $dir = self::checkUserPath($username);
        $bytes = 0;

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));

        foreach ($iterator as $i) 
        {
            $bytes += $i->getSize();
        }

        return $bytes;
    }

    public static function findFile (string $username, string $file_id): string|bool
    {
        $dir = self::checkUserPath($username);
        $list = glob($dir . '/*.*');

        if (false === $list) {
            return false;
        }

        foreach ($list as $filename) {
            $basename = basename($filename);
            if ($file_id === self::fileID($basename)) {
                return $basename;
            }
        }

        return false;
    }

    public static function listFiles (string $username)
    {
        $dir = self::checkUserPath($username);
        $list = glob($dir . '/*.*');
        $filelist = [];

        if (false === $list) {
            return false;
        }

        foreach ($list as $filename) {
            $basename = basename($filename);
            $filelist[$basename] = [
                'id' => self::fileID($basename),
                'size' => filesize($filename)
            ];
        }

        return $filelist;
    }

    public static function fileinfo (string $username, string $filename)
    {
        $userpath = self::checkUserPath($username);

        if (false === $userpath) {
            return false;
        }

        $fullname = $userpath . DIRECTORY_SEPARATOR . $filename;

        return [
            "filename" => $filename,
            'size' => filesize($fullname),
            'id' => self::fileID($filename)
        ];
    }

    public static function filesize (string $username, string $filename): int|bool 
    {
        $userpath = self::checkUserPath($username);

        if (false === $userpath) {
            return false;
        }

        return filesize($userpath . DIRECTORY_SEPARATOR . $filename);
    }

    public static function filename (string $filename): string|bool
    {
        $invchars = ['<', '>', '|', '\\', ':', '&', ';', '*', '?'];
        $filename = explode('/', $filename);
        $filename = $filename[count($filename) - 1];

        foreach ($invchars as $char) {
            if (false !== strpos($filename, $char)) {
                return false;
            }
        }

        return $filename;
    }
}