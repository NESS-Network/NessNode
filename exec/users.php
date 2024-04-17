<?php
require 'utils/autoload.php';
require 'utils/format.php';

use \modules\ness\Privateness;
use \modules\ness\lib\StorageJson;

ini_set('display_errors', 'yes');
error_reporting(E_ALL);

$json = new StorageJson();
$pr = new Privateness($json);

$result = [
    'slots' => [
        'total' => $pr->slots(),
        'free' => $pr->slotsFree()
    ],
    'users' => $pr->listLocalUsers()
];

print_r($result);