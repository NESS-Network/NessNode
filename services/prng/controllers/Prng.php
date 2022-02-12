<?php
namespace Services\prng\controllers;

use modules\emer\Emer;
use modules\worm\Worm;
use modules\ness\Privateness;
use \modules\ness\lib\StorageJson;

use internals\lib\Output;
use services\prng\exceptions\EFileNotFound;
use services\prng\models\Prng as PrngModel;

/**
 *      !!! Warning !!!
 *      Change systemd configuration for apache
 *      in /lib/systemd/apache2.service 
 *      or /usr/lib/systemd/system/httpd.service (Arch)
 *      the *** PrivateTmp=false ***
 */

class Prng {
    public function seed(string $username, $id) {
        $node_config = require '../config/node.php';
        $node_url = $node_config['url'];
        $node_nonce = $node_config['nonce'];
        $emer = new Emer();
        $prng = new PrngModel();
        $json = new StorageJson();
        $pr = new Privateness($json);

        try {
            $emer = new Emer();
            $user = $emer->findUser($username);

            if (false === $user) {
                Output::error('User "' . $username . '" not found');
                return false;
            } else {
                $user = Worm::parseUser($user['value']);
            }

            if (!$pr->isActive($username)) {
                Output::error('User is inactive');
                return false;
            }

            $res = Privateness::verifyID($id, $username, $user['nonce'], $user['verify'], $node_url, $node_nonce);

            if (true === $res) {
                Output::data(['seed' => $prng->seed()]);
            } else {
                Output::error('User auth ID FAILED');
            }
        } catch (EFileNotFound $exception) {
            Output::error($exception->getMessage());
        }
    }
    
    public function seedb() {
        $prng = new PrngModel();

        try {
            Output::data(['seed' => $prng->seedb()]);
        } catch (EFileNotFound $exception) {
            Output::error($exception->getMessage());
        }
    }

    public function numbers() {
        $prng = new PrngModel();

        try {
            Output::data(['numbers' => $prng->numbers()]);
        } catch (EFileNotFound $exception) {
            Output::error($exception->getMessage());
        }
    }

    public function numbersb() {
        $prng = new PrngModel();

        try {
            Output::data(['numbers' => $prng->numbersb()]);
        } catch (EFileNotFound $exception) {
            Output::error($exception->getMessage());
        }
    }
}