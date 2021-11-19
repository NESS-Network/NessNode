<?php
namespace services\node\models;

use modules\emer\Emer;
use modules\worm\Worm;

class User {
    public function getInfo(): Array {
        $emer = new Emer();
        $services = require __DIR__ . '/../../../etc/services.php';
        $info = $services['node'];
        $info['emercoin'] = $emer->info();

        return $info;
    }

    public function listUsers(): Array {
        $emer = new Emer();
        $result = [];
        
        $users = $emer->listUsers();
        var_dump($users);
        foreach ($users as $username => $value) {
            if (Worm::isUser($value)) {
                $result[$username] = Worm::parseUser($value);
            }
        }

        return $result;
    }

    public function findUser(string $username): Array {
        $emer = new Emer();
        $result = [];
        
        $user = $emer->findUser($username);
        if (Worm::isUser($user['value'])) {
            return Worm::parseUser($user['value']);
        }

        return false;
    }
}