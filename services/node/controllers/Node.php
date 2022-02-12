<?php
namespace services\node\controllers;

use modules\emer\exceptions\EConnectionError;
use modules\crypto\Crypto;
use internals\lib\Output;
use Base32\Base32;
use modules\ness\lib\ness;
use modules\ness\Privateness;
use modules\ness\lib\StorageJson;

class Node {
    public function info() {
        try {
            Output::info(Privateness::nodeInfo());
        } catch (EConnectionError $exception) {
            Output::error('Can not connect to emercoin');
        }
    }

    public function nodes() {
        try {
            Output::data(Privateness::nodesList());
        } catch (EConnectionError $exception) {
            Output::error('Can not connect to emercoin');
        }
    }

    public function services() {
        $services = require __DIR__ . '/../../../etc/services.php';
        Output::data($services);
    }

    public function testAuthId(string $username, $id) {
        $node_config = require '../config/node.php';
        $node_url = $node_config['url'];
        $node_nonce = $node_config['nonce'];

        try {
            $user = Privateness::usersFind($username);

            if (false === $user) {
                Output::error('User "' . $username . '" not found');
                return false;
            }

            // verify(user_public_key, “node.url-node.nonce-username-user.nonce”, authentication_id)
            $res = Privateness::verifyID($id, $username, $user['nonce'], $user['verify'], $node_url, $node_nonce);

            if (true === $res) {
                Output::message('User auth ID OK');
            } else {
                Output::error('User auth ID FAILED');
            }

        } catch (\Throwable $e) {
            Output::error($e->getMessage());
            return false;
        }
    }

    public function testAuthTwoWay() {
        $node_config = require '../config/node.php';
        $test_string = "Whoever knows how to take, to defend, the thing, to him belongs property";

        $username = $_POST['username'];

        try {
            $user = Privateness::usersFind($username);

            if (false === $user) {
                Output::error('User "' . $username . '" not found');
                return false;
            }

            $res = Privateness::verify2way($_POST['data'], $_POST['sig'], $user['verify']);

            if (false === $res) {
                Output::error('Signature check FAILED');
                return false;
            }

            $decrypted = Privateness::decrypt2way($_POST['data'], $node_config['private'], $node_config['public']);

            if ('The state calls its own violence law, but that of the individual, crime.' === $decrypted) {
                $data = $test_string;
                $sig = '';

                Privateness::encrypt2way($data, $sig, $user['public'], $node_config['private'], $node_config['verify']);
                
                Output::encrypted($data, $sig);
                return true;
            } else {
                Output::error("Signature check OK\nDecrypt FAILED");
                return false;
            }

        } catch (\Throwable $e) {
            Output::error($e->getMessage());
            return false;
        }
    }

    public function getAddress(string $username, $id) {
        $node_config = require '../config/node.php';
        $node_url = $node_config['url'];
        $node_nonce = $node_config['nonce'];

        try {
            $user = Privateness::usersFind($username);

            if (false === $user) {
                Output::error('User "' . $username . '" not found');
                return false;
            }

            // verify(user_public_key, “node.url-node.nonce-username-user.nonce”, authentication_id)
            $res = Privateness::verifyID($id, $username, $user['nonce'], $user['verify'], $node_url, $node_nonce);

            if (true === $res) {
                $json = new StorageJson();
                $pr = new Privateness($json);
                $addr = $pr->getUserAddress($username);
                Output::data(['address' => $addr]);
            } else {
                Output::error('User auth ID FAILED');
            }

        } catch (\Throwable | \Error $e) {
            Output::error($e->getMessage());
            return false;
        }
    }

    public function balance(string $username, $id) {
        $node_config = require '../config/node.php';
        $node_url = $node_config['url'];
        $node_nonce = $node_config['nonce'];

        try {
            $user = Privateness::usersFind($username);

            if (false === $user) {
                Output::error('User "' . $username . '" not found');
                return false;
            }

            // verify(user_public_key, “node.url-node.nonce-username-user.nonce”, authentication_id)
            $res = Privateness::verifyID($id, $username, $user['nonce'], $user['verify'], $node_url, $node_nonce);

            if (true === $res) {
                $json = new StorageJson();
                $pr = new Privateness($json);
                $balance = $pr->balance($username);
        
                Output::data(['balance' => $balance]);
            } else {
                Output::error('User auth ID FAILED');
            }

        } catch (\Throwable $e) {
            Output::error($e->getMessage());
            return false;
        }
    }

    public function userinfo(string $username, $id) {
        $node_config = require '../config/node.php';
        $node_url = $node_config['url'];
        $node_nonce = $node_config['nonce'];

        try {
            $user = Privateness::usersFind($username);

            if (false === $user) {
                Output::error('User "' . $username . '" not found');
                return false;
            }

            // verify(user_public_key, “node.url-node.nonce-username-user.nonce”, authentication_id)
            $res = Privateness::verifyID($id, $username, $user['nonce'], $user['verify'], $node_url, $node_nonce);

            if (true === $res) {
                $json = new StorageJson();
                $pr = new Privateness($json);
                $userinfo = $pr->userinfo($username);
        
                Output::data(['userinfo' => $userinfo]);
            } else {
                Output::error('User auth ID FAILED');
            }

        } catch (\Throwable $e) {
            Output::error($e->getMessage());
            return false;
        }
    }

    public function withdraw() {
        $node_config = require '../config/node.php';

        $username = $_POST['username'];

        // Verification

        try {
            $user = Privateness::usersFind($username);

            if (false === $user) {
                Output::error('User "' . $username . '" not found');
                return false;
            }

            $res = Privateness::verify2way($_POST['data'], $_POST['sig'], $user['verify']);

            if (false === $res) {
                Output::error('Signature check FAILED');
                return false;
            }

            $decrypted = Privateness::decrypt2way($_POST['data'], $node_config['private'], $node_config['public']);
            $wdata = json_decode($decrypted, true);

            if (!is_array($wdata)) {
                Output::error("Signature check OK\nDecrypt FAILED");
                return false;
            }

        } catch (\Exception $e) {
            Output::error($e->getMessage());
            return false;
        }

        // Sending coins and hours

        $coins = (float) $wdata['coins'];
        $hours = (float) $wdata['hours'];
        $to_addr = (string) $wdata['to_addr'];

        try {

            $json = new StorageJson();
            $pr = new Privateness($json);

            $balance = $pr->balance($username);

            if ($coins > $balance['coins']) {
                throw new \Exception("You want to withdraw $coins coins this is more than available ("
                    . $balance['coins'] . ")");
            }

            if ($hours > $balance['available']) {
                throw new \Exception("You want to withdraw $hours coin - hours this is more than available ("
                    . $balance['available'] . ")");
            }

            $pr->withdrawUser($username, $coins, $hours, $to_addr);

            // Output::message("Signature check OK\nDecrypt OK");

            $data = "The user $username withdrawed $coins coins and $hours hours to $to_addr";
            $sig = '';

            Privateness::encrypt2way($data, $sig, $user['public'], $node_config['private'], $node_config['verify']);

            Output::encrypted($data, $sig);

        } catch (\Throwable $e) {
            Output::error($e->getMessage());
            return false;
        }

        return true;
    }

    public function testNess() {
        $json = new StorageJson();
        $pr = new Privateness($json);
        var_dump( $pr->getUserAddress('master') );
        var_dump($pr->balance('master'));
        var_dump($pr->getUserAddress('ZXC'));
        var_dump($pr->balance('ZXC'));
    }

    public function pub() {
        $node_config = require '../config/node.php';
        ob_clean();
        echo trim($node_config['public']);
    }

    public function man() {
        Output::text(file_get_contents(__DIR__ . '/../../../etc/manual.txt'));
    }
}
