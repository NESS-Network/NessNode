<?php
require 'utils/autoload.php';
require 'utils/format.php';

use \modules\ness\Privateness;
use \modules\ness\lib\StorageJson;

ini_set('display_errors', 'yes');
error_reporting(E_ALL);

$json = new StorageJson();
$pr = new Privateness($json);

if ($argc >= 2) {
    $username = $argv[1];

    if (!$pr->userExists($username)) {
        formatPrintLn(['green'], '......');
        $pr->getUserAddress($username);
        formatPrint(['green'], 'User ');
        formatPrint(['b', 'green'], $username);
        formatPrintLn(['green'], ' created OK');
    } else {
        formatPrint(['red'], 'User ');
        formatPrint(['b', 'red'], $username);
        formatPrintLn(['red'], ' already exists');
    }
} else {
    formatPrintLn(['b', 'red'], 'No username');
    formatPrintLn(['green'], 'Usage create-username.php <username> [address]');
    formatPrint(['green'], 'Where ');
    formatPrint(['green', 'b'], 'username ');
    formatPrint(['green'], 'is name of test user to test payments');
    formatPrintLn();
}