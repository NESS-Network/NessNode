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
    if ($pr->userExists($username)) {
        for ($i = $pr->userCounter($username); $i <= $pr->userRandomHours($username); $i++) {
            if ($pr->payUser($username) ) {
                formatPrint(['green'], ' PAYED !');
            } else {
                formatPrint(['green'], '+');
            }
        }
            
        formatPrintLn();
    } else {
        formatPrint(['yellow'], 'User ');
        formatPrint(['yellow', 'b'], $username);
        formatPrint(['yellow'], ' does not exist');
        formatPrintLn();
    }
} else {
    formatPrintLn(['b', 'red'], 'No username');
    formatPrintLn(['green'], 'Usage test.php username');
    formatPrint(['green'], 'Where ');
    formatPrint(['green', 'b'], 'username ');
    formatPrint(['green'], 'is name of test user to test payments');
    formatPrintLn();
}