<?php
namespace services\files\controllers;

use modules\emer\exceptions\EConnectionError;
use modules\crypto\Crypto;
use internals\lib\Output;
use Base32\Base32;

use services\files\lib\Files;

use modules\ness\lib\ness;
use modules\ness\Privateness;
use modules\ness\Creator;

class File {

    public function __construct()
    {
        try {

        } catch (\Throwable $e) {
            Output::error($e->getMessage());
            die();
        }
    }

    public function man()
    {
        Output::text(file_get_contents(__DIR__ . '/../../../etc/manual.txt'));
    }

    public function quota(string $username, $id)
    {
        try {
            $pr = Creator::Privateness();
            $user = $pr->findUser($username);

            if (false === $user) {
                Output::error('User "' . $username . '" not found');
                return false;
            }

            // verify(user_public_key, “node.url-node.nonce-username-user.nonce”, authentication_id)
            $res = $pr->verifyUserId($id, $user);

            if (true === $res) {
                $files_config = Files::loadConfig();

                $quota = strtolower($files_config['quota']);

                $total = Files::translateQuota($quota);
                $used = Files::calcSpace($user->getUsername());
                $free = $total - $used;

                Output::data(['quota' => [
                    'total' => $total,
                    'used' => $used,
                    'free' => $free
                ]]);
            } else {
                Output::error('User auth ID FAILED');
            }
        } catch (\Throwable $e) {
            Output::error($e->getMessage());
            return false;
        }
    }

    public function list(string $username, $id)
    {
        try {
            $pr = Creator::Privateness();
            $user = $pr->findUser($username);

            if (false === $user) {
                Output::error('User "' . $username . '" not found');
                return false;
            }

            // verify(user_public_key, “node.url-node.nonce-username-user.nonce”, authentication_id)
            $res = $pr->verifyUserId($id, $user);

            if (true === $res) {
                Output::data(['files' => Files::listFiles($user->getUsername())]);
            } else {
                Output::error('User auth ID FAILED');
            }
        } catch (\Throwable $e) {
            Output::error($e->getMessage());
            return false;
        }
    }
}
