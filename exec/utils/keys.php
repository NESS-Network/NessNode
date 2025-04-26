<?php

class Keys {
    public static string $directory = __DIR__ . '/../../in';

    public static function findNodeFile(): bool|array
    {
        $files = self::listDirectory();

        foreach ($files as $filename) {
            $data = json_decode(file_get_contents($filename), true);

            if (false === $data) {
                continue;
            }

            if ( self::checkNodeFile($data) ) {
                return $data;
            }
        }

        return false;
    }

    public static function findUsersFile(): bool|array
    {
        $files = self::listDirectory();

        foreach ($files as $filename) {
            $data = json_decode(file_get_contents($filename), true);

            if (false === $data) {
                continue;
            }

            if ( self::checkUsersFile($data) ) {
                return $data;
            }
        }

        return false;
    }

    public static function findGeneratedConfig(): bool|array
    {
        $files = self::listDirectory();

        foreach ($files as $filename) {
            $data = json_decode(file_get_contents($filename), true);

            if (false === $data) {
                continue;
            }

            if ( self::checkGeneratedConfig($data) ) {
                return $data;
            }
        }

        return false;
    }

    public static function findSeed(): bool|string
    {
        $files = self::listDirectory();

        foreach ($files as $filename) {
            if ('seed.txt' === $filename) {
                return file_get_contents($filename);
            }
        }

        return false;
    }

    private static function checkNodeFile(array $data): bool
    {
        return isset($data['filedata']) 
            && isset($data['filedata']['vendor']) && "Privateness" === $data['filedata']['vendor']
            && isset($data['filedata']['type']) && "key" === $data['filedata']['type']
            && isset($data['filedata']['for']) && "node" === $data['filedata']['for'];
    }

    private static function checkUsersFile(array $data): bool
    {
        return isset($data['filedata']) 
            && isset($data['filedata']['vendor']) && "Privateness" === $data['filedata']['vendor']
            && isset($data['filedata']['type']) && "key" === $data['filedata']['type']
            && isset($data['filedata']['for']) && "user" === $data['filedata']['for'];
    }

    private static function checkGeneratedConfig(array $data): bool
    {
        return isset($data["slots"])
            && isset($data["quota"])
            && isset($data["wallet_password"])
            && isset($data["rpc_user"])
            && isset($data["rpc_password"]);
    }

    private static function listDirectory(): bool|array
    {
        return glob(self::$directory . '/*.json');
    }
}