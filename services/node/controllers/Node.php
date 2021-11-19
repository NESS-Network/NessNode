<?php
namespace services\node\controllers;

use modules\emer\exceptions\EConnectionError;
use modules\crypto\Crypto;
use internals\lib\Output;
use services\node\models\Node as NodeModel;
use services\node\models\User as UserModel;
use Base32\Base32;

class Node {
    public function info() {
        $node = new NodeModel();
        
        try {
            Output::info($node->getInfo());
        } catch (EConnectionError $exception) {
            Output::error('Can not connect to emercoin');
        }
    }

    public function nodes() {
        $node = new NodeModel();
        
        try {
            Output::data($node->listNodes());
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
            $user = new UserModel();
            $user = $user->findUser($username);

            if (false === $user) {
                Output::error('User "' . $username . '" not found');
                return false;
            }

            // verify(user_public_key, “node.url-node.nonce-username-user.nonce”, authentication_id)
            $message = $node_url . '-' . $node_nonce . '-' . $username . '-' . $user['nonce'];
            $res = sodium_crypto_sign_verify_detached(Base32::decode($id) , $message , base64_decode($user['verify']));

            if (true === $res) {
                Output::message('User auth ID OK');
            } else {
                Output::error('User auth ID FAILED');
            }

        } catch (\Exception $e) {
            Output::error($e->getMessage());
            return false;
        }
    }

    public function testAuthTwoWay() {
        $node_config = require '../config/node.php';
        $test_string = "Whoever knows how to take, to defend, the thing, to him belongs property";

        // var_dump($_POST);

        try {
            $user = new UserModel();
            $user = $user->findUser($_POST['username']);

            $res = sodium_crypto_sign_verify_detached(Base32::decode($_POST['sig']), $_POST['data'], base64_decode($user['verify']));

            if (false === $res) {
                Output::error('Signature check FAILED');
                return false;
            }

            $keypair = sodium_crypto_box_keypair_from_secretkey_and_publickey(base64_decode($node_config['private']), base64_decode($node_config['public']));
            $decrypted = sodium_crypto_box_seal_open(base64_decode($_POST['data']), $keypair);

            if ('The state calls its own violence law, but that of the individual, crime.' === $decrypted) {
                // Output::message("Signature check OK\nDecrypt OK");
                $data = sodium_crypto_box_seal($test_string, base64_decode($user['public']));
                $data = base64_encode($data);
                $keypair = sodium_crypto_box_keypair_from_secretkey_and_publickey(base64_decode($node_config['private']), base64_decode($node_config['verify']));
                $sig = sodium_crypto_sign_detached($data, $keypair);

                // $res = sodium_crypto_sign_verify_detached($sig, $data, base64_decode($node_config['verify']));
                // var_dump($node_config['verify'], $res);

                $sig = Base32::encode($sig);
                Output::encrypted($data, $sig);
                return true;
            } else {
                Output::error("Signature check OK\nDecrypt FAILED");
                return false;
            }

        } catch (\Exception $e) {
            Output::error($e->getMessage());
            return false;
        }
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
