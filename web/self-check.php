<?php
ini_set('display_errors', 'no');
error_reporting(0);

try {
    require __DIR__ . '/../config/ness.php';
    
    echo 'OK';
} catch (\Error $ex) {
    echo $ex->getMessage();
}
