<?php

namespace modules\emer;

use modules\emer\lib\Emercoin;
use modules\emer\exceptions\EConnectionError;
use modules\emer\exceptions\EUserNotFound;
use modules\emer\exceptions\ENodeNotFound;

class Emer
{

    private $config;

    /**
     * Config initialisation
     */
    public function __construct()
    {
        $this->config = require __DIR__ . '/../../config/emercoin.php';

        Emercoin::$username = $this->config['user'];
        Emercoin::$password = $this->config['password'];
        Emercoin::$address = $this->config['host'];
        Emercoin::$port = $this->config['port'];
    }

    public function info(): array
    {
        try {
            return Emercoin::getinfo();
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            if (strpos($message, 'Can\'t connect to') !== false) {
                throw new EConnectionError($this->config, $message);
            } else {
                throw new \Exception($message);
            }
        }
    }

    public function findUser(string $username): array
    {
        try {
            /** @var array $user  */
            $user = Emercoin::name_show('worm:user:ness:' . $username, 'base64');
            $user['value'] = base64_decode($user['value']);

            return $user;
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            if (strpos($message, 'Can\'t connect to') !== false) {
                throw new EConnectionError($this->config, $message);
            } elseif (strpos($message, 'failed to read from name DB') !== false) {
                throw new EUserNotFound($username);
            } else {
                throw new \Exception($message);
            }

            return false;
        }
    }

    public function findNode(string $url, bool $showFull = false)
    {
        try {
            /** @var array $node  */
            $node = Emercoin::name_show('worm:node:ness:' . $url, 'base64');

            if ($showFull) {
                $node['value'] = base64_decode($node['value']);

                return $node;
            } else {
                return base64_decode($node['value']);
            }
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            if (strpos($message, 'Can\'t connect to') !== false) {
                throw new EConnectionError($this->config, $message);
            } elseif (strpos($message, 'failed to read from name DB') !== false) {
                throw new ENodeNotFound($url);
            } else {
                throw new \Exception($message);
            }

            return false;
        }
    }

    public function listNodes(): array
    {
        try {
            /** @var array $nodes  */
            $nodes = Emercoin::name_filter('^worm:node:ness:.+', 0, 0, 0, '', 'base64');
            $result = [];

            foreach ($nodes as $node) {
                $name = $node['name'];
                $name = explode(':', $name);
                $name = array_slice($name, 3);
                $name = implode(':', $name);

                $value = base64_decode($node['value']);
                $result[$name] = $value;
            }

            return $result;
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            if (strpos($message, 'Can\'t connect to') !== false) {
                throw new EConnectionError($this->config, $message);
            } else {
                throw new \Exception($message);
            }

            return false;
        }
    }

    public function listUsers(): array
    {
        try {
            /** @var array $nousersdes  */
            $users = Emercoin::name_filter('^worm:user:ness:.+', 0, 0, 0, '', 'base64');
            $result = [];

            foreach ($users as $user) {
                $name = $user['name'];
                $name = explode(':', $name);
                $name = array_slice($name, 3);
                $name = implode(':', $name);

                $value = base64_decode($user['value']);
                $result[$name] = $value;
            }

            return $result;
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            if (strpos($message, 'Can\'t connect to') !== false) {
                throw new EConnectionError($this->config, $message);
            } else {
                throw new \Exception($message);
            }

            return false;
        }
    }
}
