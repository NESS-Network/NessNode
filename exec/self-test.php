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

$hdf = __DIR__ . '/../homedir';

if (file_exists($hdf)) {
    $ness_dir = file_get_contents($hdf);
} else {
    $homedir = posix_getpwuid(getmyuid())['dir'];
    $ness_dir = $homedir . "/.ness";
}

// Config test
formatPrintLn(['green', 'b'], ' *** Config test');

$filename = $ness_dir . "/emer.json";
if (file_exists($filename)) {
    formatPrintLn(['green'], "File $filename OK");
} else {
    formatPrintLn(['red'], "File $filename NOT FOUND");
    exit(1);
}

$filename = $ness_dir . "/ness.json";
if (file_exists($filename)) {
    formatPrintLn(['green'], "File $filename OK");
} else {
    formatPrintLn(['red'], "File $filename NOT FOUND");
    exit(1);
}

$filename = $ness_dir . "/node.json";
if (file_exists($filename)) {
    formatPrintLn(['green'], "File $filename OK");
} else {
    formatPrintLn(['red'], "File $filename NOT FOUND");
    exit(1);
}

$filename = $ness_dir . "/files.json";
if (file_exists($filename)) {
    formatPrintLn(['green'], "File $filename OK");
} else {
    formatPrintLn(['red'], "File $filename NOT FOUND");
    exit(1);
}

$filename = $ness_dir . "/prng.json";
if (file_exists($filename)) {
    formatPrintLn(['green'], "File $filename OK");
} else {
    formatPrintLn(['red'], "File $filename NOT FOUND");
    exit(1);
}

$filename = $ness_dir . "/data/users.json";
if (file_exists($filename)) {
    formatPrintLn(['green'], "File $filename OK");
} else {
    formatPrintLn(['red'], "File $filename NOT FOUND");
    exit(1);
}

// Config load

$config = require __DIR__ . '/../config/ness.php';
$node = require __DIR__ . '/../config/ness.php';

$nodefile = $ness_dir . "/node.json";
$nodedata = json_decode(file_get_contents($nodefile), true);
$url = $nodedata['url'] . '/node/nodes';
$master = $nodedata['master-user'];

// EMC test
formatPrintLn(['green', 'b'], ' *** EMC test');

try {
    $emer = new Emer();
    $user = $emer->findUser($master);
    $nodes = $emer->listNodes();
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
$usersfile = $ness_dir . "/data/users.json";

if (file_exists($usersfile)) {
    $users_data = json_decode(file_get_contents($usersfile), true);

    if (isset($users_data[$master])) {
        formatPrintLn(['green'], "Master user FOUND");
    } else {
        formatPrintLn(['red'], "Master user NOT FOUND");
    }
} else {
    formatPrintLn(['red'], "File $usersfile NOT FOUND");
}

// Ping test
formatPrintLn(['green', 'b'], ' *** Ping test');

try {
    $contents = file_get_contents($url);
    $data = json_decode($contents, true);
    
    if ($data['result'] == 'data') {
        formatPrintLn(['green'], "Ping OK");
    } else {
        formatPrintLn(['red'], "Ping failed (result error)");
        echo $contents;
    }

} catch (Exception $exception) {
    formatPrintLn(['red'], "Ping failed (connection error)");
}
