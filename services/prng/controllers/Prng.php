<?php

namespace Services\prng\controllers;

use Exception;
use modules\worm\Worm;
use modules\ness\Privateness;
use \modules\ness\lib\StorageJson;
use modules\ness\Creator;

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
    private PrngModel $prng;
    private Privateness $privateness;

    public function __construct()
    {
        $this->prng = new PrngModel();
        $this->privateness = Creator::Privateness();
    }

    public function seed(string $username, $id)
    {
        try {
            $user = $this->privateness->findUser($username);
            
            if (false === $user) {
                Output::error('User "' . $username . '" not found');
                return false;
            }

            if (!$this->privateness->IsActiveOrMaster($user->getUsername())) {
                Output::error('User "' . $username . '" is inactive');
                return false;
            }

            $res = $this->privateness->verifyUserId($id, $user);

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
            $user = $this->privateness->findUser($username);

            if (false === $user) {
                Output::error('User "' . $username . '" not found');
                return false;
            }

            if (!$this->privateness->IsActiveOrMaster($user->getUsername())) {
                Output::error('User "' . $username . '" is inactive');
                return false;
            }

            $res = $this->privateness->verifyUserId($id, $user);

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
            $user = $this->privateness->findUser($username);

            if (false === $user) {
                Output::error('User "' . $username . '" not found');
                return false;
            }

            if (!$this->privateness->IsActiveOrMaster($user->getUsername())) {
                Output::error('User "' . $username . '" is inactive');
                return false;
            }

            $res = $this->privateness->verifyUserId($id, $user);

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
            $user = $this->privateness->findUser($username);

            if (false === $user) {
                Output::error('User "' . $username . '" not found');
                return false;
            }

            if (!$this->privateness->IsActiveOrMaster($user->getUsername())) {
                Output::error('User "' . $username . '" is inactive');
                return false;
            }

            $res = $this->privateness->verifyUserId($id, $user);

            if (true === $res) {
                Output::data(['numbersb' => $this->prng->numbersb()]);
            } else {
                Output::error('User auth ID FAILED');
            }
        } catch (\Throwable $exception) {
            Output::error($exception->getMessage());
        }
    }
    
    public function i256(string $username, $id)
    {
        try {
            $user = $this->privateness->findUser($username);

            if (false === $user) {
                Output::error('User "' . $username . '" not found');
                return false;
            }

            if (!$this->privateness->IsActiveOrMaster($user->getUsername())) {
                Output::error('User "' . $username . '" is inactive');
                return false;
            }

            $res = $this->privateness->verifyUserId($id, $user);

            if (true === $res) {
                Output::data(['numbers' => $this->prng->numbers256i()]);
            } else {
                Output::error('User auth ID FAILED');
            }
        } catch (\Throwable $exception) {
            Output::error($exception->getMessage());
        }
    }
    
    public function h256(string $username, $id)
    {
        try {
            $user = $this->privateness->findUser($username);

            if (false === $user) {
                Output::error('User "' . $username . '" not found');
                return false;
            }

            if (!$this->privateness->IsActiveOrMaster($user->getUsername())) {
                Output::error('User "' . $username . '" is inactive');
                return false;
            }

            $res = $this->privateness->verifyUserId($id, $user);

            if (true === $res) {
                Output::data(['numbers' => $this->prng->numbers256h()]);
            } else {
                Output::error('User auth ID FAILED');
            }
        } catch (\Throwable $exception) {
            Output::error($exception->getMessage());
        }
    }
}
