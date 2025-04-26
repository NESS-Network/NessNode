<?php
require 'utils/autoload.php';
require 'utils/format.php';
require 'utils/keys.php';

use \modules\ness\Privateness;
use \modules\ness\lib\StorageJson;
use \modules\ness\lib\ness as ness;

ini_set('display_errors', 'yes');
error_reporting(E_ALL);

$homedir = posix_getpwuid(getmyuid())['dir'];

if ('/root' === $homedir) {
    $directory = '/home/ness';

    if (!file_exists($directory)) {
        mkdir($directory);
        chmod($directory, 0777);
    }
} else {
    $directory = $homedir . "/.ness";
}

file_put_contents(__DIR__ . '/../homedir', $directory);

formatPrintLn(['green'], "Scanning " . Keys::$directory . " diractory");

$config = Keys::findGeneratedConfig();

if (false === $config) {
    formatPrintLn(['red', 'b'], "Generated config not found");
    exit(1);
} else {
    formatPrintLn(['green', 'b'], "Generated config found");
}

$node = Keys::findNodeFile();

if (false === $node) {
    formatPrintLn(['red', 'b'], "Node key not found");
    exit(1);
} else {
    formatPrintLn(['green', 'b'], "Node key found");
}

$user = Keys::findUsersFile();

if (false === $user) {
    formatPrintLn(['red', 'b'], "Master user key not found");
    exit(1);
} else {
    formatPrintLn(['green', 'b'], "Master user key found");
}

$seed = Keys::findSeed();

if (false === $seed) {
    formatPrintLn(['red', 'b'], "Seed file (seed.txt) not found");
    exit(1);
} else {
    formatPrintLn(['green', 'b'], "Seed file found");
}

die(0);

/**
 * Creating wallet
 */


 $ness = new ness();
 $wallet = $ness->createWallet("Ness node wallet", $config['wallet_password'], $seed);

 if (false !== $wallet) {
    formatPrintLn(['green', 'b'], 'Wallet CREATED ' . $wallet['meta']['filename']);
    formatPrintLn(['green', 'b'], ' first address: ' . $wallet['entries'][0]['address']);
 } else {
    formatPrintLn(['red', 'b'], "Wallet creation FAILED");
    exit(1);
 }


 $wallet_name = $wallet['meta']['filename'];

/**
 * Writing node config ...
 */


// ness
$filename = $directory . '/ness.json';
$data = [
    "host" => "localhost",
    "password"  => $config['wallet_password'],
    "port"  => 6660,
    "wallet_id"  =>   $wallet_name
];

file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));
chmod($filename, 0644);

// emer
$filename = $directory . '/emer.json';
$data = [
    "host" => "localhost",
    "password" => $config['rpc_password'],
    "port" => $config['rpc_port'],
    "user" => $config['rpc_user']
];

file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));
chmod($filename, 0644);

// node
$filename = $directory . '/node.json';
$data = [
    "services" => $node['services'],
    "delta" => "4",
    "master-user" => $node['master-user'],
    "nonce" => $node['nonce'],
    "period" => "24",
    "private" => $node['keys']['private'],
    "public" => $node['keys']['public'],
    "slots" => $config['slots'],
    "tariff" => $node['tariff'],
    "url" => $node['url'],
    "network" => $node['network'],
    "verify" => $node['keys']['verify']
];

file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));
chmod($filename, 0644);

// files
$filename = $directory . '/files.json';
$data = [
    "dir" => "storage",
    "quota" => $config['quota'],
    "salt" => base64_encode(random_bytes(32))
];

file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));
chmod($filename, 0666);

$storage_dir = __DIR__ . "/../services/files/storage";

if (!file_exists($storage_dir)) {
    mkdir($storage_dir);
    chmod($filename, 0777);
}

// prng
$filename = $directory . '/prng.json';
$data = [
    "numbers" => "/tmp/numbers.json",
    "numbers-big" => "/tmp/numbers-big.json",
    "numbers-i256" => "/tmp/i256.json",
    "numbers-h256" => "/tmp/h256.json",
    "seed" => "/tmp/seed.txt",
    "seed-big" => "/tmp/seed-big.txt"
];

file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));
chmod($filename, 0644);

formatPrintLn(['green', 'b'], "Config files have been written to $directory");

/**
 * Writing Master user ...
 */

$data_dir = $directory . "/data";
$log_dir = $directory . "/log";
$users_config_file = $data_dir . "/users.json";
$users_data = [];

if (!file_exists($data_dir)) {
    mkdir($data_dir);
    chmod($directory, 0777);
}

if (!file_exists($log_dir)) {
    mkdir($log_dir);
    chmod($directory, 0777);
}

$config = require __DIR__ . '/../config/ness.php';
$node_config = require __DIR__ . '/../config/node.php';

ness::$host = $config['host'];
ness::$port = $config['port'];
ness::$wallet_id = $config['wallet_id'];
ness::$password = $config['password'];

if (file_exists($users_config_file)) {
    $users_data = json_decode(file_get_contents($users_config_file), true);
}

$ness = new ness();
$addr = $ness->createAddr();
$addr = $addr['addresses'][0];
$shadowname = md5($userdata['username'] . "+$node_config[url]+$node_config[nonce]+$node_config[private]:" . time());

$users_data[$userdata['username']] = [
    'addr' => $addr,
    'shadowname' => $shadowname
];

file_put_contents($users_config_file, json_encode($users_data, JSON_PRETTY_PRINT));
chmod($users_config_file, 0666);

formatPrintLn(['green', 'b'], 'Master user created and registered in ' . $users_config_file);


/**
 * NODE and USER
 * WORM and NVS
 */

 var_dump($node, $user);

 formatPrintLn(['green', 'b'], "USER NVS");
 formatPrintLn(['green'], 'worm:user:ness:' . $user['username']);

 formatPrintLn(['green', 'b'], "USER <WORM>");
 formatPrintLn(['green'], $user['worm']);

 formatPrintLn(['green', 'b'], "NODE NVS");
 formatPrintLn(['green'], 'worm:node:ness:' . $node['url']);

 formatPrintLn(['green', 'b'], "NODE <WORM>");
 formatPrintLn(['green'], $node['worm']);