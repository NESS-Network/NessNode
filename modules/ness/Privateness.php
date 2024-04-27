<?php

namespace modules\ness;

use Base32\Base32;
use internals\lib\Output;
use modules\ness\lib\ness as ness;
use modules\ness\interfaces\Storage;

use modules\emer\Emer;
use modules\worm\Worm;
use modules\ness\User;

use modules\ness\exceptions\EUserDontExist;
use modules\ness\exceptions\EMasterUserDontExist;
use modules\ness\exceptions\EUserInsuficientFunds;
use modules\ness\exceptions\EUsersAddrFileDontExist;
use modules\ness\exceptions\EMasterUserInsuficientFunds;

/**
 * Ness API module
 *
 * @author Aleksej Sokolov <aleksej000@gmail.com>,<chosenone111@protonmail.com>
 */
class Privateness
{

    private array $config;
    private array $node_config;
    private array $users;
    private array $payments;

    private Storage $storage;

    public static $output = [];

    private string $host;
    private string $nonce;

    private string $network;
    private array $services;

    private string $private;
    private string $public;
    private string $verify;

    private int $tariff;
    private int $period;
    private int $delta;

    /**
     * Config initialisation
     *
     * @param Storage $storage
     */
    public function __construct(Storage $storage)
    {
        $this->config = require __DIR__ . '/../../config/ness.php';
        $this->node_config = require __DIR__ . '/../../config/node.php';

        $this->storage = $storage;

        $this->tariff = (int) $this->node_config['tariff'];

        if (empty($this->tariff)) {
            $this->tariff  = 1;
        }

        $this->period = (int) $this->node_config['period'];

        if (empty($this->period)) {
            $this->period  = 720;
        }

        $this->delta = (int) $this->node_config['delta'];

        if (empty($this->delta)) {
            $this->delta  = 120;
        }

        $this->users = $this->storage->readUsers();
        $this->payments = $this->storage->readPayments();

        $this->host = $this->node_config['url'];
        $this->nonce = $this->node_config['nonce'];

        $this->network = $this->node_config['network'];
        $this->services = $this->node_config['services'];

        $this->private = $this->node_config['private'];
        $this->public = $this->node_config['public'];
        $this->verify = $this->node_config['verify'];

        ness::$host = $this->config['host'];
        ness::$port = $this->config['port'];
        ness::$wallet_id = $this->config['wallet_id'];
        ness::$password = $this->config['password'];
    }

    public function buildURL(string $service, string $method)
    {
        return $this->host . '/' . $service . '/' . $method;
    }

    /**
     * Random amount of hours until next user payment
     *
     * @return int
     */
    private function getRandomCounterHours()
    {
        return $this->period - $this->delta + rand(0, $this->delta * 2);
    }

    /**
     * Master user username
     *
     * @return string
     */
    public function getMasterUser()
    {
        return $this->node_config['master-user'];
    }

    /**
     * Is user Master user
     *
     * @param string $username
     * @return boolean
     */
    public function isMasterUser(string $username)
    {
        return $username === $this->getMasterUser();
    }

    /**
     * Full users list
     *
     * @return string
     */
    public function getUsersDetailed()
    {
        $users = $this->users;

        foreach ($users as $username => $user) {
            unset ($users[$username]['shadowname']);

            if (isset($this->payments[$username])) {
                $users[$username]['payments'] = $this->payments[$username];
            } else {
                $users[$username]['payments'] = [];
            }

            $users[$username]['active'] = $this->isActive($username);

        }

        return $users;
    }

    /**
     * Generate users shadowname
     * Shadowname = MD5(Username-node.url-node.nonce-username-user.nonce)
     * 
     * @param string $username
     * @return boolean
     */
    private function generateShadowname(User $user): string
    {
        return md5($user->getUsername() . "-$this->host-$this->nonce-$this->private" . $user->getNonce() . ': ' . time());
    }

    /**
     * Registered user address
     *
     * @param string $username
     * @return void
     */
    public function register(User $user)
    {
        $username = $user->getUsername();
    
        if ($this->isMasterUser($username)) {
            if (empty($this->users[$username])) {
                $ness = new ness();

                $result = $ness->createAddr();
                $addr = $result['addresses'][0];

                $this->storage->writeUser($username, $addr, 0, 0, $this->generateShadowname($user));
                $this->users = $this->storage->readUsers();

                return true;
            } else {
                return false;
            }
        } else {
            if (empty($this->users[$username])) {
                $ness = new ness();

                $result = $ness->createAddr();
                $addr = $result['addresses'][0];

                $this->storage->writeUser($username, $addr, 0, $this->getRandomCounterHours(), $this->generateShadowname($user));
                $this->users = $this->storage->readUsers();
                file_put_contents(__DIR__ . '/../../log/log.txt', json_encode($this->users));
                return true;
            } else {
                return false;
            }
        }
    } 
    
    function registerUsername(string $username): bool
    {
        if (empty($this->users[$username])) {
            $ness = new ness();

            $result = $ness->createAddr();
            $addr = $result['addresses'][0];
            $shadowname = md5($username . "+$this->host+$this->nonce+123456789:" . time());
            $this->storage->writeUser($username, $addr, 0, $this->getRandomCounterHours(), $shadowname);
            $this->users = $this->storage->readUsers();
            file_put_contents(__DIR__ . '/../../log/log.txt', json_encode($this->users));
            return true;
        } else {
            return false;
        }
    }

    /**
     * Verifying users Auth-ID
     *
     * @param string $authID
     * @param string $username
     * @param string $user_nonce
     * @param string $user_verify
     * @param string $node_url
     * @param string $node_nonce
     * @return bool
     */
    public static function verifyID(string $authID, string $username, string $user_nonce, string $user_verify, string $node_url, string $node_nonce)
    {
        $message = $node_url . '-' . $node_nonce . '-' . $username . '-' . $user_nonce;
        return sodium_crypto_sign_verify_detached(Base32::decode($authID), $message, base64_decode($user_verify));
    }

    public function verifyUserId(string $authID, User $user)
    {
        $message = $this->host . '-' . $this->nonce . '-' . $user->getUsername() . '-' . $user->getNonce();
        // echo $message; die();
        // $secret_key = base64_decode("lZo9hnpOqOm/UBhII6ooK42fFyaV1wHJ8YXYPpWrTZs=");
        // $verify_key = base64_decode($user->getVerify());
        // $keypair = sodium_crypto_box_keypair_from_secretkey_and_publickey($secret_key, $verify_key);
        // $alt_id = sodium_crypto_sign_detached($message, $keypair);
        // echo Base32::encode($alt_id); die();
        return sodium_crypto_sign_verify_detached(Base32::decode($authID), $message, base64_decode($user->getVerify()));
    }

    public static function verifyAlternativeID(string $alternativeID, string $username, string $user_nonce, string $user_verify, string $node_url, string $node_nonce)
    {
        // verify(user_public_key, “node.url-node.nonce-username-user.nonce-alternative”, alternative_authentication_id)
        $message = $node_url . '-' . $node_nonce . '-' . $username . '-' . $user_nonce . '-alternative';
        return sodium_crypto_sign_verify_detached(Base32::decode($alternativeID), $message, base64_decode($user_verify));
    }

    public function verifyAlternativeUserId(string $alternativeID, User $user)
    {
        // verify(user_public_key, “node.url-node.nonce-username-user.nonce-alternative”, alternative_authentication_id)
        $message = $this->host . '-' . $this->nonce . '-' . $user->getUsername() . '-' . $user->getNonce() . '-alternative';
        return sodium_crypto_sign_verify_detached(Base32::decode($alternativeID), $message, base64_decode($user->getVerify()));
    }

    /**
     * Two-Way-Authentication: verifying Auth-ID
     *
     * @param string $data
     * @param string $sig
     * @param string $user_verify
     * @return bool
     */
    public static function verify2way(string $data, string $sig, string $user_verify)
    {
        return sodium_crypto_sign_verify_detached(Base32::decode($sig), $data, base64_decode($user_verify));
    }

    public function verifyUser2way(string $data, string $sig, User $user)
    {
        return sodium_crypto_sign_verify_detached(Base32::decode($sig), $data, base64_decode($user->getVerify()));
    }

    /**
     * Two-Way-Authentication: decrypting data from user to node
     *
     * @param string $data
     * @param string $node_private
     * @param string $node_pub
     * @return ?string
     */
    public static function decrypt2way(string $data, string $node_private, string $node_pub)
    {
        $keypair = sodium_crypto_box_keypair_from_secretkey_and_publickey(base64_decode($node_private), base64_decode($node_pub));
        return sodium_crypto_box_seal_open(base64_decode($data), $keypair);
    }

    public function decryptUser2way(string $data)
    {
        $keypair = sodium_crypto_box_keypair_from_secretkey_and_publickey(base64_decode($this->private), base64_decode($this->public));
        return sodium_crypto_box_seal_open(base64_decode($data), $keypair);
    }

    /**
     * Two-Way-Authentication: encrypting data from node to user
     *
     * @param string $data inputs data and returns encrypted data
     * @param string $sig returns signature
     * @param string $user_pub
     * @param string $node_priv
     * @param string $node_verify
     * @return void
     */
    public static function encrypt2way(string &$data, string &$sig, string $user_pub, string $node_priv, string $node_verify)
    {
        $data = sodium_crypto_box_seal($data, base64_decode($user_pub));
        $data = base64_encode($data);
        $keypair = sodium_crypto_box_keypair_from_secretkey_and_publickey(base64_decode($node_priv), base64_decode($node_verify));
        $sig = sodium_crypto_sign_detached($data, $keypair);
        $sig = Base32::encode($sig);
    }

    public function encryptUser2way(string &$data, string &$sig, User $user)
    {
        $data = sodium_crypto_box_seal($data, base64_decode($user->getPublic()));
        $data = base64_encode($data);
        $keypair = sodium_crypto_box_keypair_from_secretkey_and_publickey(base64_decode($this->private), base64_decode($this->verify));
        $sig = sodium_crypto_sign_detached($data, $keypair);
        $sig = Base32::encode($sig);
    }

    /**
     * Withdraw coins and hours from user
     *
     * @param string $from_username
     * @param string $to_addr
     * @param float $coins
     * @param integer $hours
     * @return void
     */
    public function withdraw(string $from_username, string $to_addr, float $coins, int $hours)
    {
        $ness = new ness();
        // Check coin ammount
        $balance = $this->balance($from_username);
        if (($balance['coins'] < ($coins + 0.000001)) || ($balance['hours'] < ($hours+1))) {
            throw new EUserInsuficientFunds($from_username, $balance['coins'], $balance['hours'], $coins, $hours);
        }

        // Send coins and hours
        return $ness->send($this->users[$from_username], $to_addr, $coins, $hours);
    }

    /**
     * Send coins and hours
     *
     * @param string $from_addr
     * @param string $to_addr
     * @param float $coins
     * @param integer $hours
     * @return void
     */
    public function send(string $from_addr, string $to_addr, float $coins, int $hours)
    {
        $ness = new ness();
        $result = $ness->send($from_addr, $to_addr, $coins, $hours);
        self::$output = ness::$output;

        return $result;
    }

    /**
     * User coins and hours
     *
     * @param string $username
     * @return array
     */
    public function balance(string $username): array
    {
        $ness = new ness();

        // Check user existance
        if (!isset($this->users[$username]) || !isset($this->users[$username]['addr'])) {
            throw new EUserDontExist($username);
        }

        $result = $ness->getBalance($this->users[$username]['addr']);
        $balance = $result['confirmed'];
        $balance['coins'] = number_format($balance['coins'] / 1000000, 6);
        $balance['fee'] = $ness->getFee($balance['hours']);
        $balance['available'] = $balance['hours'] - $balance['fee'];

        return $balance;
    }

    /**
     * Transactions list
     *
     * @param string $username
     * @return array
     */
    public function transactions(string $username): array
    {
        $ness = new ness();

        // Check user existance
        if (!isset($this->users[$username]) || !isset($this->users[$username]['addr'])) {
            throw new EUserDontExist($username);
        }

        return $ness->transactions($this->users[$username]['addr']);
    }

    /**
     * Userinfo
     *
     * @param string $username
     * @return array
     */
    public function userinfo(string $username): array
    {
        $ness = new ness();

        // Check user existance
        if (!isset($this->users[$username]) || !isset($this->users[$username]['addr'])) {
            throw new EUserDontExist($username);
        }

        $userinfo = $this->users[$username];
        unset($userinfo['random_hours']);
        $userinfo['balance'] = $this->balance($username);
        $userinfo['joined'] = $this->joined($username);
        $userinfo['is_active'] = $this->isActive($username);
        $userinfo['is_master'] = $this->isMasterUser($username);

        return $userinfo;
    }

    /**
     * Payments
     *
     * @param string $username
     * @return array
     */
    public function payments(string $username): array
    {
        // Check user existance
        if (!isset($this->users[$username]) || !isset($this->users[$username]['addr'])) {
            throw new EUserDontExist($username);
        }

        return $this->payments;
    }

    /**
     * User existance
     *
     * @param string $username
     * @return boolean
     */
    public function userExists(string $username): bool
    {
        return (isset($this->users[$username]) && isset($this->users[$username]['addr']));
    }

    /**
     * Has user enough coin-hours to pay for counter
     *
     * @param string $username
     * @return boolean
     */
    public function isActive(string $username): bool
    {
        // return true;
        //Check user existance
        if (!isset($this->users[$username]) || !isset($this->users[$username]['addr'])) {
            return false;
        }

        if ($this->isMasterUser($username)) {
            return true;
        }

        $balance = $this->balance($username);
        
        return (($this->node_config['tariff'] * $this->users[$username]['counter'] + 1) <= $balance['hours']);
    }

    /**
     * Is user Active or is User a Master User
     *
     * @param string $username
     * @return boolean
     */
    public function IsActiveOrMaster(string $username): bool
    {
        return $this->isActive($username) || $this->isMasterUser($username);
    }

    /**
     * Return counter for this user
     *
     * @param string $username
     * @return integer
     */
    public function userCounter(string $username): int
    {
        //Check user existance
        if (!isset($this->users[$username]) || !isset($this->users[$username]['addr'])) {
            throw new EUserDontExist($username);
        }

        return $this->users[$username]['counter'];
    }

    /**
     * Return random generated hours for this user
     *
     * @param string $username
     * @return integer
     */
    public function userRandomHours(string $username): int
    {
        //Check user existance
        if (!isset($this->users[$username]) || !isset($this->users[$username]['addr'])) {
            throw new EUserDontExist($username);
        }

        return $this->users[$username]['random_hours'];
    }

    /**
     * Check and pay user (is counter equals random hours)
     *
     * @param string $username
     * @return boolean
     */
    public function payUser(string $username): bool
    {
        $ness = new ness();

        // Check existance of master user
        $master = $this->getMasterUser();

        if (!isset($this->users[$master]) || !isset($this->users[$master]['addr'])) {
            throw new EMasterUserDontExist($master);
        }

        $master_addr = $this->users[$master]['addr'];
        $addr = $this->users[$username]['addr'];
        $counter = (int) $this->users[$username]['counter'];
        $random_hours = (int) $this->users[$username]['random_hours'];

        if ($counter >= $random_hours) {
            $coin_hours = $this->node_config['tariff'] * $counter;
            $txid = $ness->send($addr, $master_addr, 0.000001, $coin_hours);

            $this->storage->writePayment($username, date('Y-m-d H:i:s'), $counter, $coin_hours, $txid);
            self::$output = ness::$output;

            $this->storage->writeUser($username, '', 0, $this->getRandomCounterHours());
            $this->users = $this->storage->readUsers();
            $this->payments = $this->storage->readPayments();

            return true;
        } else {
            $counter++;
            $this->users[$username]['counter'] = $counter;
            $this->storage->writeUser($username, '', $counter);

            return false;
        }
    }

    /**
     * Withdraw coins and hours from user to external address
     *
     * @param string $username
     * @param float $coins
     * @param integer $hours
     * @param string $to_addr
     * @return boolean
     */
    public function withdrawUser(string $username, float $coins, int $hours, string $to_addr): bool
    {
        $ness = new ness();
        $addr = $this->users[$username]['addr'];
        $ness->send($addr, $to_addr, $coins, $hours);

        return true;
    }

    /**
     * Check and pay all users
     *
     * @return void
     */
    public function payUsers()
    {
        foreach ($this->users as $username => $user) {
            if (!$this->isMasterUser($username) && $this->isActive($username)) {
                $this->payUser($username);
            }
        }
    }

    /**
     * All info about node from blockchain
     */
    public function nodeInfo(): array
    {
        $emer = new Emer();
        $files = require __DIR__ . '/../../config/files.php';
        $info['files']['quota'] = $files['quota'];
        $info['network'] = $this->network;
        $info['services'] = $this->services;
        $info['emercoin'] = $emer->info();
        $info['slots'] = self::slots();
        $info['slots_free'] = self::slotsFree();
        $info['tariff'] = $this->tariff;
        $info['period'] = $this->period;
        $info['delta'] = $this->delta;

        return $info;
    }

    /**
     * List all nodes from blockchain
     */
    public static function nodesList(): array
    {
        $emer = new Emer();
        $result = [];

        $nodes = $emer->listNodes();
        // var_dump($nodes);
        foreach ($nodes as $name => $value) {
            if (Worm::isNode($value)) {
                $result[$name] = Worm::parseNode($value);
            }
        }

        return $result;
    }

    /**
     * Find node in blockchain
     */
    public static function nodesFind(string $node_name)
    {
        $emer = new Emer();

        $node = $emer->findNode($node_name);
        if (Worm::isNode($node['value'])) {
            return Worm::parseNode($node['value']);
        }

        return false;
    }

    /**
     * List all users from blockchain
     */
    public static function usersList(): array
    {
        $emer = new Emer();
        $result = [];

        $users = $emer->listUsers();
        // var_dump($users);
        foreach ($users as $username => $value) {
            if (Worm::isUser($value)) {
                $result[$username] = Worm::parseUser($value);
            }
        }

        return $result;
    }

    /**
     * Find user in blockchain
     */
    public static function usersFind(string $username)
    {
        $emer = new Emer();

        $user = $emer->findUser($username);
        if (Worm::isUser($user['value'])) {
            return Worm::parseUser($user['value']);
        }

        return false;
    }

    /**
     * Find user everywhere by username
     */
    public function findUser(string $username): User|bool
    {
        $emer = new Emer();

        $users = $this->listLocalUsers();
        
        foreach ($users as $user_name => $local_user) {
            if ($user_name === $username) {
                $user = $emer->findUser($username);
                if (!empty($user['value']) && Worm::isUser($user['value'])) {
                    $user = Worm::parseUser($user['value']);
                    return new User($username, $local_user['addr'], $local_user['shadowname'], $user['type'], $user['nonce'], $user['tags'], $user['public'], $user['verify']);
                }
            }
        }

        $user = $emer->findUser($username);

        if (false !== $user && Worm::isUser($user['value'])) {
            $user = Worm::parseUser($user['value']);
            return new User($username, '', '', $user['type'], $user['nonce'], $user['tags'], $user['public'], $user['verify']);
        }

        return false;
    }

    /**
     * Find user localy by shadowname
     */
    public function findShadow(string $shadowname): User|bool
    {
        $emer = new Emer();

        $users = $this->listLocalUsers();
        
        foreach ($users as $username => $local_user) {
            if (isset($local_user['shadowname']) && ($shadowname === $local_user['shadowname'])) {
                $user = $emer->findUser($username);
                if (Worm::isUser($user['value'])) {
                    $user = Worm::parseUser($user['value']);
                    return new User($username, $local_user['addr'], $shadowname, $user['type'], $user['nonce'], $user['tags'], $user['public'], $user['verify']);
                }
            }
        }

        return false;
    }

    public function slots(): int
    {
        return (int) $this->node_config['slots'];
    }

    public function slotsFree(): int
    {
        $slots_used = 0;

        foreach ($this->users as $username => $user) {
            if ($this->joined($username)) {
                $slots_used ++;
            }
        }

        return $this->slots() - $slots_used;
    }

    public function joined(string $username): bool
    {
        return $this->userExists($username);
    }

    public function in(string $username): bool
    {
        return $this->userExists($username) && !empty($this->transactions($username));
    }

    public function listLocalUsers(): array
    {
        $users = $this->users;

        foreach ($users as $username => $user) {
            $users[$username]['joined'] = $this->joined($username);   
            $users[$username]['is_active'] = $this->isActive($username);   
        }

        return $users;
    }
}
