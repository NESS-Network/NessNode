<?php

namespace Services\prng\controllers;

use Exception;
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

class Prng
{
    private string $node_url;
    private string $node_nonce;
    private PrngModel $prng;
    private Privateness $privateness;

    public function __construct()
    {
        $node_config = require '../config/node.php';
        $this->node_url = $node_config['url'];
        $this->node_nonce = $node_config['nonce'];
        $this->prng = new PrngModel();
        $json = new StorageJson();
        $this->privateness = new Privateness($json);
    }

    public function seed(string $username, $id)
    {
        try {
            $user = Privateness::usersFind($username);

            if (false === $user) {
                Output::error('User "' . $username . '" not found');
                return false;
            }

            if (!$this->privateness->isActive($username)) {
                Output::error('User "' . $username . '" is inactive');
                return false;
            }

            $res = Privateness::verifyID($id, $username, $user['nonce'], $user['verify'], $this->node_url, $this->node_nonce);

            if (true === $res) {
                Output::data(['seed' => $this->prng->seed()]);
            } else {
                Output::error('User auth ID FAILED');
            }
        } catch (\Throwable $exception) {
            Output::error($exception->getMessage());
        }
    }

    public function seedb(string $username, $id)
    {
        try {
            $user = Privateness::usersFind($username);

            if (false === $user) {
                Output::error('User "' . $username . '" not found');
                return false;
            }

            if (!$this->privateness->isActive($username)) {
                Output::error('User "' . $username . '" is inactive');
                return false;
            }

            $res = Privateness::verifyID($id, $username, $user['nonce'], $user['verify'], $this->node_url, $this->node_nonce);

            if (true === $res) {
                Output::data(['seedb' => $this->prng->seedb()]);
            } else {
                Output::error('User auth ID FAILED');
            }
        } catch (\Throwable $exception) {
            Output::error($exception->getMessage());
        }
    }
    
    public function numbers(string $username, $id)
    {
        try {
            $user = Privateness::usersFind($username);

            if (false === $user) {
                Output::error('User "' . $username . '" not found');
                return false;
            }

            if (!$this->privateness->isActive($username)) {
                Output::error('User "' . $username . '" is inactive');
                return false;
            }

            $res = Privateness::verifyID($id, $username, $user['nonce'], $user['verify'], $this->node_url, $this->node_nonce);

            if (true === $res) {
                Output::data(['numbers' => $this->prng->numbers()]);
            } else {
                Output::error('User auth ID FAILED');
            }
        } catch (\Throwable $exception) {
            Output::error($exception->getMessage());
        }
    }

    public function numbersb(string $username, $id)
    {
        try {
            $user = Privateness::usersFind($username);

            if (false === $user) {
                Output::error('User "' . $username . '" not found');
                return false;
            }

            if (!$this->privateness->isActive($username)) {
                Output::error('User "' . $username . '" is inactive');
                return false;
            }

            $res = Privateness::verifyID($id, $username, $user['nonce'], $user['verify'], $this->node_url, $this->node_nonce);

            if (true === $res) {
                Output::data(['numbersb' => $this->prng->numbersb()]);
            } else {
                Output::error('User auth ID FAILED');
            }
        } catch (\Throwable $exception) {
            Output::error($exception->getMessage());
        }
    }
}
