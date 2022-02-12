<?php
namespace modules\ness;

use Base32\Base32;
use modules\ness\lib\ness as ness;
use modules\ness\interfaces\Storage;

use modules\emer\Emer;
use modules\worm\Worm;

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
class Privateness {

    private array $config;
    private array $node_config;
    private array $users;
    private array $payments;

    private Storage $storage;

    public static $output = [];

    /**
     * Config initialisation
     *
     * @param Storage $storage
     */
    public function __construct(Storage $storage) {
        $this->config = require 'config/ness.php';
        $this->node_config = require 'config/node.php';

        $this->storage = $storage;

        $this->tariff = (int) $this->node_config['tariff'];

        if (empty($this->tariff)) {
            $this->tariff  = 24;
        }

        $this->period = (int) $this->node_config['period'];

        if (empty($this->period)) {
            $this->period  = 7200;
        }

        $this->delta = (int) $this->node_config['delta'];

        if (empty($this->delta)) {
            $this->delta  = 200;
        }

        $this->users = $this->storage->readUsers();
        $this->payments = $this->storage->readPayments();

        ness::$host = $this->config['host'];
        ness::$port = $this->config['port'];
        ness::$wallet_id = $this->config['wallet_id'];
        ness::$password = $this->config['password'];
    }

    /**
     * Random amount of hours until next user payment
     *
     * @return int
     */
    private function getRandomCounterHours() {
        return $this->period - $this->delta + rand(0, $this->delta*2);
    }

    /**
     * Master user username
     *
     * @return string
     */
    public function getMasterUser() {
        return $this->node_config['master-user'];
    }

    /**
     * Is user Master user
     *
     * @param string $username
     * @return boolean
     */
    public function isMasterUser(string $username) {
        return $username === $this->getMasterUser();
    }

    /**
     * Registered user address
     *
     * @param string $username
     * @return void
     */
    public function getUserAddress(string $username) {
        if ($this->isMasterUser($username)) {
            if (empty($this->users[$username])) {
                $ness = new ness();
                $result = $ness->createAddr();
                $addr = $result['addresses'][0];

                $this->storage->writeUser($username, $addr);

                return $addr;
            } else {
                $user = $this->users[$username];
                return $user['addr'];
            }
        } else {
            if (empty($this->users[$username])) {
                $ness = new ness();

                $result = $ness->createAddr();
                $addr = $result['addresses'][0];

                $this->storage->writeUser($username, $addr, 0, $this->getRandomCounterHours());

                return $addr;
            } else {
                return $this->users[$username]["addr"];
            }
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
    public static function verifyID(string $authID, string $username, string $user_nonce, string $user_verify, string $node_url, string $node_nonce) {
        $message = $node_url . '-' . $node_nonce . '-' . $username . '-' . $user_nonce;
        return sodium_crypto_sign_verify_detached(Base32::decode($authID) , $message , base64_decode($user_verify));
    }

    /**
     * Two-Way-Authentication: verifying Auth-ID
     *
     * @param string $data
     * @param string $sig
     * @param string $user_verify
     * @return bool
     */
    public static function verify2way(string $data, string $sig, string $user_verify) {
        sodium_crypto_sign_verify_detached(Base32::decode($sig), $data, base64_decode($user_verify));
    }

    /**
     * Two-Way-Authentication: decrypting data from user to node
     *
     * @param string $data
     * @param string $node_private
     * @param string $node_pub
     * @return string|false
     */
    public static function decrypt2way(string $data, string $node_private, string $node_pub) {
        $keypair = sodium_crypto_box_keypair_from_secretkey_and_publickey(base64_decode($node_private), base64_decode($node_pub));
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
    public static function encrypt2way(string &$data, string &$sig, string $user_pub, string $node_priv, string $node_verify) {
        $data = sodium_crypto_box_seal($data, base64_decode($user_pub));
        $data = base64_encode($data);
        $keypair = sodium_crypto_box_keypair_from_secretkey_and_publickey(base64_decode($node_priv), base64_decode($node_verify));
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
    public function withdraw(string $from_username, string $to_addr, float $coins, int $hours) {
        $ness = new ness();
        // Check coin ammount
        $balance = $this->balance($from_username);
        if ( ($balance['coins'] < ($coins + 0.001)) || ($balance['hours'] < $hours) ) {
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
    public function send(string $from_addr, string $to_addr, float $coins, int $hours) {
        $ness = new ness();
        $result = $ness->send($from_addr, $to_addr, $coins, $hours);
        self::$output = ness::$output;

        return $result;
    }

    /**
     * User coins and hours
     *
     * @param string $username
     * @return void
     */
    public function balance(string $username) {
        $ness = new ness();

        // Check user existance
        if (!isset($this->users[$username]) || !isset($this->users[$username]['addr'])) {
            throw new EUserDontExist($username);
        }

        $result = $ness->getBalance($this->users[$username]['addr']);
        $balance = $result['confirmed'];
        $balance['fee'] = $ness->getFee($balance['hours']);
        $balance['available'] = $balance['hours'] - $balance['fee'];

        return $balance;
    }

    /**
     * Userinfo
     *
     * @param string $username
     * @return void
     */
    public function userinfo(string $username) {
        $ness = new ness();

        // Check user existance
        if (!isset($this->users[$username]) || !isset($this->users[$username]['addr'])) {
            throw new EUserDontExist($username);
        }

        $userinfo = $this->users[$username];
        unset($userinfo['random_hours']);
        $userinfo['balance'] = $this->balance($username);
        $userinfo['is_active'] = $this->isActive($username);

        return $userinfo;
    }

    /**
     * User existance
     *
     * @param string $username
     * @return boolean
     */
    public function userExists(string $username): bool {
        return (isset($this->users[$username]) && isset($this->users[$username]['addr']));
    }

    /**
     * Has user enough coin-hours to pay for counter
     *
     * @param string $username
     * @return boolean
     */
    public function isActive(string $username): bool {
        //Check user existance
        if (!isset($this->users[$username]) || !isset($this->users[$username]['addr'])) {
            throw new EUserDontExist($username);
        }

        $balance = $this->balance($username);

        return (0 < $balance['hours']);
    }

    /**
     * Return counter for this user
     *
     * @param string $username
     * @return integer
     */
    public function userCounter(string $username): int {
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
    public function userRandomHours(string $username): int {
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
    public function payUser(string $username): bool {
        $ness = new ness();

        // Check existance of master user
        $master = $this->getMasterUser();

        if (!isset($this->users[$master]) || !isset($this->users[$master]['addr']))  {
            throw new EMasterUserDontExist($master);
        }

        $master_addr = $this->users[$master]['addr'];
        $addr = $this->users[$username]['addr'];
        $counter = (int) $this->users[$username]['counter'];
        $random_hours = (int) $this->users[$username]['random_hours'];

        if ($counter >= $random_hours) {
            $coin_hours = $this->node_config['tariff'] * $counter;
            $txid = $ness->send($addr, $master_addr, 0.001, $coin_hours);

            $this->storage->writeUser($username, '', 0, $this->getRandomCounterHours());
            $this->users = $this->storage->readUsers();

            $this->storage->writePayment($username, date('Y-m-d H:i:s'), $counter, $coin_hours, $txid);
            $this->payments = $this->storage->readPayments();
            self::$output = ness::$output;
            
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
    public function withdrawUser(string $username, float $coins, int $hours, string $to_addr): bool {
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
    public function payUsers() {
        foreach ($this->users as $username => $user) {
            $this->payUser($username);
        }
    }

    public static function nodeInfo(): Array {
        $emer = new Emer();
        $services = require __DIR__ . '/../../../etc/services.php';
        $info = $services['node'];
        $info['emercoin'] = $emer->info();

        return $info;
    }

    public static function nodesList(): Array {
        $emer = new Emer();
        $result = [];
        
        $nodes = $emer->listNodes();

        foreach ($nodes as $name => $value) {
            if (Worm::isNode($value)) {
                $result[$name] = Worm::parseNode($value);
            }
        }

        return $result;
    }

    public static function nodesFind(string $node_name): Array|bool {
        $emer = new Emer();
        
        $node = $emer->findNode($node_name);
        if (Worm::isNode($node['value'])) {
            return Worm::parseNode($node['value']);
        }

        return false;
    }

    public static function usersList(): Array {
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

    public static function usersFind(string $username): Array|bool {
        $emer = new Emer();
        
        $user = $emer->findUser($username);
        if (Worm::isUser($user['value'])) {
            return Worm::parseUser($user['value']);
        }

        return false;
    }

}