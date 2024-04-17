<?php
require 'utils/autoload.php';
require 'utils/format.php';

use \modules\ness\Privateness;
use \modules\ness\lib\StorageJson;
use \modules\emer\Emer;
use \modules\emer\exceptions\EConnectionError;
use modules\ness\lib\ness;

ini_set('display_errors', 'yes');
error_reporting(E_ALL - E_DEPRECATED - E_WARNING);

$config = require __DIR__ . '/../config/ness.php';

// Config test
formatPrintLn(['green', 'b'], ' *** Config test');
$directory = posix_getpwuid(getmyuid())['dir'];

$filename = $directory . "/.ness/emer.json";
if (file_exists($filename)) {
    formatPrintLn(['green'], "File $filename OK");
} else {
    formatPrintLn(['red'], "File $filename NOT FOUND");
}

$filename = $directory . "/.ness/ness.json";
if (file_exists($filename)) {
    formatPrintLn(['green'], "File $filename OK");
} else {
    formatPrintLn(['red'], "File $filename NOT FOUND");
}

$filename = $directory . "/.ness/node.json";
if (file_exists($filename)) {
    formatPrintLn(['green'], "File $filename OK");
} else {
    formatPrintLn(['red'], "File $filename NOT FOUND");
}

$filename = $directory . "/.ness/files.json";
if (file_exists($filename)) {
    formatPrintLn(['green'], "File $filename OK");
} else {
    formatPrintLn(['red'], "File $filename NOT FOUND");
}

$filename = $directory . "/.ness/prng.json";
if (file_exists($filename)) {
    formatPrintLn(['green'], "File $filename OK");
} else {
    formatPrintLn(['red'], "File $filename NOT FOUND");
}

$filename = $directory . "/.ness/data/users.json";
if (file_exists($filename)) {
    formatPrintLn(['green'], "File $filename OK");
} else {
    formatPrintLn(['red'], "File $filename NOT FOUND");
}

// EMC test
formatPrintLn(['green', 'b'], ' *** EMC test');

try {
    $emer = new Emer();
    $user = $emer->findUser('master');
    formatPrintLn(['green'], "Emercoin OK");
} catch (EConnectionError $exception) {
    formatPrintLn(['red'], "Emercoin connection ERROR");
}

// Ness test
formatPrintLn(['green', 'b'], ' *** Ness test');

try {
    ness::$host = $config['host'];
    ness::$port = $config['port'];
    ness::$wallet_id = $config['wallet_id'];
    ness::$password = $config['password'];

    $ness = new ness();
    $ness->getWallets();
} catch (Exception $exception) {
    formatPrintLn(['red'], "Privateness connection ERROR");
}

formatPrintLn(['green'], "Privateness OK");

// Master user
formatPrintLn(['green', 'b'], ' *** Master user test');
$usersfile = $directory . "/.ness/data/users.json";

if (file_exists($usersfile)) {
    $users_data = json_decode(file_get_contents($usersfile), true);

    if (isset($users_data['master'])) {
        formatPrintLn(['green'], "Master user FOUND");
    } else {
        formatPrintLn(['red'], "Master user NOT FOUND");
    }
} else {
    formatPrintLn(['red'], "File $usersfile NOT FOUND");
}

// Ping test
formatPrintLn(['green', 'b'], ' *** Ping test');

$nodefile = $directory . "/.ness/node.json";
$nodedata = json_decode(file_get_contents($nodefile), true);
$url = $nodedata['url'] . '/node/nodes';

try {
    $contents = file_get_contents($url);
    $data = json_decode($contents, true);
    
    if ($data['result'] == 'data') {
        formatPrintLn(['green'], "Ping OK");
    } else {
        formatPrintLn(['red'], "Ping failed (result error)");
    }

} catch (Exception $exception) {
    formatPrintLn(['red'], "Ping failed (connection error)");
}
