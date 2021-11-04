<?php
namespace modules\crypto;

class Crypto {

    public static function encrypt(string $data, $key): array {
        // ...

        return [];
    }

    public static function decrypt(string $encrypted, $key, $iv): string {
        // ...

        return '';
    }

    public static function sign(string $data, $priv_key): string {
        // ...

        return '';
    }

    public static function verify(string $data, $sig, $pub_key): bool {
        // ...

        return false;
    }

}
