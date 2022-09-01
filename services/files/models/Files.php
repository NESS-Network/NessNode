<?php
namespace services\files\models;

use services\files\exceptions\ECantCreatePath;
use services\files\exceptions\EConfigError;

class Files {

    public static function loadConfig (): array 
    {
        $files_config = require __DIR__ . '/../../../config/files.php';

        if (!isset($files_config['quota'])) {
            throw new EConfigError("files.json", "quota");
        }

        if (!isset($files_config['dir'])) {
            throw new EConfigError("files.json", "dir");
        }

        return $files_config;
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

    public static function listFiles (string $username): array|bool 
    {
        $dir = self::checkUserPath($username);

        return glob($dir . '/*.*');
    }
}