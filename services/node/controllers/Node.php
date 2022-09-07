<?php

namespace services\node\controllers;

use modules\emer\exceptions\EConnectionError;
use modules\crypto\Crypto;
use internals\lib\Output;
use Base32\Base32;
use modules\ness\lib\ness;
use modules\ness\Privateness;
use modules\ness\lib\StorageJson;
use modules\ness\Creator;
use services\files\exceptions\EConfigError;

class Node
{
    public function info()
    {
        try {
            Output::info(Privateness::nodeInfo());
        } catch (EConnectionError $exception) {
            Output::error('Can not connect to emercoin');
        }
    }

    public function nodes()
    {
        try {
            Output::data(Privateness::nodesList());
        } catch (EConnectionError $exception) {
            Output::error('Can not connect to emercoin');
        }
    }

    public function services()
    {
        $services = require __DIR__ . '/../../../etc/services.php';
        Output::data($services);
    }

    public function testAuthId(string $username, $id)
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
                Output::message('User auth ID OK');
            } else {
                Output::error('User auth ID FAILED');
            }
        } catch (\Throwable $e) {
            Output::error($e->getMessage());
            return false;
        }
    }

    public function testAuthShadowId(string $shadowname, $id)
    {
        try {
            $pr = Creator::Privateness();
            $user = $pr->findShadow($shadowname);

            if (false === $user) {
                Output::error('User "' . $shadowname . '" not found');
                return false;
            }

            // verify(user_public_key, “node.url-node.nonce-username-user.nonce”, authentication_id)
            $res = $pr->verifyUserId($id, $user);
            
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

    public function testAuthTwoWay()
    {
        try {
            $test_string = "Whoever knows how to take, to defend, the thing, to him belongs property";

            $username = $_POST['username'];

            $pr = Creator::Privateness();
            $user = $pr->findUser($username);

            if (false === $user) {
                Output::error('User "' . $username . '" not found');
                return false;
            }

            $res = $pr->verifyUser2way($_POST['data'], $_POST['sig'], $user);

            if (false === $res) {
                Output::error('Signature check FAILED');
                return false;
            }

            $decrypted = $pr->decryptUser2way($_POST['data']);

            if ('The state calls its own violence law, but that of the individual, crime.' === $decrypted) {
                $data = $test_string;
                $sig = '';

                $pr->encryptUser2way($data, $sig, $user);

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

    public function testAuthShadowTwoWay()
    {
        try {
            $test_string = "The state calls its own violence law, but that of the individual, crime.";

            $shadowname = $_POST['username'];

            $pr = Creator::Privateness();
            $user = $pr->findShadow($shadowname);

            if (false === $user) {
                Output::error('User "' . $shadowname . '" not found');
                return false;
            }

            $res = $pr->verifyUser2way($_POST['data'], $_POST['sig'], $user);

            if (false === $res) {
                Output::error('Signature check FAILED');
                return false;
            }

            $decrypted = $pr->decryptUser2way($_POST['data']);

            if ('Whoever knows how to take, to defend, the thing, to him belongs property' === $decrypted) {
                $data = $test_string;
                $sig = '';

                $pr->encryptUser2way($data, $sig, $user);

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

    public function join()
    {
        try {
            $username = $_POST['username'];

            $pr = Creator::Privateness();
            $user = $pr->findUser($username);

            if (false === $user) {
                Output::error('User "' . $username . '" not found');
                return false;
            }

            $res = $pr->verifyUser2way($_POST['data'], $_POST['sig'], $user);

            if (true === $res) {
                if (!$pr->register($user)) {
                    Output::error('User olready joined');
                }

                $user = $pr->findUser($username);
                // Output::data(['address' => $addr, 'shadowname' => $user->getShadowname()]);
                $data = json_encode([
                    'address' => $user->getAddress(), 
                    'shadowname' => $user->getShadowname()
                ]);
                $sig = '';

                $pr->encryptUser2way($data, $sig, $user);

                Output::encrypted($data, $sig);
            } else {
                Output::error('User verification FAILED');
            }
        } catch (\Throwable | \Error $e) {
            Output::error($e->getMessage());
            return false;
        }
    }

    public function joined()
    {
        try {
            $username = $_POST['username'];

            $pr = Creator::Privateness();
            $user = $pr->findUser($username);

            if (false === $user) {
                Output::error('User "' . $username . '" not found');
                return false;
            }

            $res = $pr->verifyUser2way($_POST['data'], $_POST['sig'], $user);

            if (true === $res) {
                $joined = $pr->joined($user->getUsername());

                if ($joined) {
                    $data = json_encode([
                            'joined' => true,
                            'address' => $user->getAddress(), 
                            'shadowname' => $user->getShadowname()
                    ]);
                } else {
                    $data = json_encode(['joined' => false]);
                }

                $sig = '';

                $pr->encryptUser2way($data, $sig, $user);
                Output::encrypted($data, $sig);
            } else {
                Output::error('Signature check FAILED');
            }
        } catch (\Throwable | \Error $e) {
            Output::error($e->getMessage());
            return false;
        }
    }

    public function balance()
    {
        try {
            $shadowname = $_POST['shadowname'];

            $pr = Creator::Privateness();
            $user = $pr->findShadow($shadowname);

            if (false === $user) {
                Output::error('User "' . $shadowname . '" not found');
                return false;
            }

            $res = $pr->verifyUser2way($_POST['data'], $_POST['sig'], $user);

            if (true === $res) {
                $balance = $pr->balance($user->getUsername());

                $data = json_encode(['balance' => $balance]);
                $sig = '';
    
                $pr->encryptUser2way($data, $sig, $user);
                Output::encrypted($data, $sig);
            } else {
                Output::error('Signature check FAILED');
            }
        } catch (\Throwable $e) {
            Output::error($e->getMessage());
            return false;
        }
    }

    public function userinfo()
    {
        try {
            $shadowname = $_POST['shadowname'];

            $pr = Creator::Privateness();
            $user = $pr->findShadow($shadowname);

            if (false === $user) {
                Output::error('User "' . $shadowname . '" not found');
                return false;
            }

            $res = $pr->verifyUser2way($_POST['data'], $_POST['sig'], $user);

            if (true === $res) {
                $userinfo = $pr->userinfo($user->getUsername());

                $data = json_encode(['userinfo' => $userinfo]);
                $sig = '';
    
                $pr->encryptUser2way($data, $sig, $user);
                Output::encrypted($data, $sig);
            } else {
                Output::error('Signature check FAILED');
            }
        } catch (\Throwable $e) {
            Output::error($e->getMessage());
            return false;
        }
    }

    public function withdraw()
    {
        try {
            $shadowname = $_POST['shadowname'];

            $pr = Creator::Privateness();
            $user = $pr->findShadow($shadowname);

            // Verification

            if (false === $user) {
                Output::error('User "' . $shadowname . '" not found');
                return false;
            }

            $res = $pr->verifyUser2way($_POST['data'], $_POST['sig'], $user);

            if (false === $res) {
                Output::error('Signature check FAILED');
                return false;
            }

            $decrypted = $pr->decryptUser2way($_POST['data']);
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
            $balance = $pr->balance($user->getUsername());

            if ($coins > $balance['coins']) {
                throw new \Exception("You want to withdraw $coins coins this is more than available ("
                    . $balance['coins'] . ")");
            }

            if ($hours > $balance['available']) {
                throw new \Exception("You want to withdraw $hours coin - hours this is more than available ("
                    . $balance['available'] . ")");
            }

            $pr->withdrawUser($user->getUsername(), $coins, $hours, $to_addr);

            // Output::message("Signature check OK\nDecrypt OK");

            $data = "The user " . $user->getUsername() . " withdrawed $coins coins and $hours hours to $to_addr";
            $sig = '';

            $pr->encryptUser2way($data, $sig, $user);

            Output::encrypted($data, $sig);
        } catch (\Throwable $e) {
            Output::error($e->getMessage());
            return false;
        }

        return true;
    }

    public function pub()
    {
        $node_config = require __DIR__ . '/../../../config/node.php';
        ob_clean();
        echo trim($node_config['public']);
    }

    public function verify()
    {
        $node_config = require __DIR__ . '/../../../config/node.php';
        ob_clean();
        echo trim($node_config['verify']);
    }

    public function slots()
    {
        $node_config = require __DIR__ . '/../../../config/node.php';

        try {
            if (!isset($node_config['slots'])) {
                throw new EConfigError('config/node.php', 'slots');
            }

            $pr = Creator::Privateness();
            $users_count = count($pr->listLocalUsers());

            ob_clean();

            echo json_encode([
                'slots' => [
                    'total' => (int)$node_config['slots'],
                    'used' => $users_count,
                    'free' => (int)$node_config['slots'] - $users_count,
                ]
            ]);
        } catch (\Throwable $e) {
            Output::error($e->getMessage());
            return false;
        }

        return true;
    }

    public function man()
    {
        Output::text(file_get_contents(__DIR__ . '/../../../etc/manual.txt'));
    }
}
