<?php
require 'utils/autoload.php';
require 'utils/format.php';

use \modules\ness\Privateness;
use \modules\ness\lib\StorageJson;
use \modules\emer\Emer;
use \modules\emer\lib\JsonRpcClient;
use \modules\emer\exceptions\EConnectionError;
use \modules\emer\exceptions\EUserNotFound;
use modules\ness\lib\ness;
use \services\prng\models\Prng as PrngModel;

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

JsonRpcClient::$debug = False;

try {
    $emer = new Emer();
    $nodes = $emer->listNodes();
    formatPrintLn(['green'], "Nodes list OK");
    $user = $emer->findUser($master);
    formatPrintLn(['green'], "Master user FOUND");
} catch (EConnectionError $exception) {
    formatPrintLn(['red'], "Emercoin connection ERROR");
} catch (EUserNotFound $exception) {
    formatPrintLn(['red'], "Master user not found");
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

// PRNG test
formatPrintLn(['green', 'b'], ' *** PRNG test');

try {
    $prng = new PrngModel();
    $numbers = $prng->numbers();
    
    if (count($numbers)) {
        formatPrintLn(['green'], "PRNG OK");
    }

} catch (Exception $exception) {
    formatPrintLn(['red'], "PRNG failed");
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

//File storage test
$storage_dir = __DIR__ . "/../services/files/storage";

if (!file_exists($storage_dir)) {
    mkdir($storage_dir);
    chmod($filename, 0777);
}

$perm = fileperms($storage_dir);
if (! (0777 === ($perm & 0777)) ) {
    formatPrintLn(['red'], "$storage_dir wrong rights (".decoct($perm).")");
} else {
    formatPrintLn(['green'], "$storage_dir rights 0777 OK");
}
