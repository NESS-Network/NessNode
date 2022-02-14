<?php
 
$filename_ness = posix_getpwuid(getmyuid())['dir'] . '/.ness/ness.json';

if (!file_exists($filename_ness)) {
    throw new \Error("File '~/.ness/ness.json' does not exist !\nMake sure you have copied configuration from 'NessNodeTester/out/config' directory");
}

$filename_users = posix_getpwuid(getmyuid())['dir'] . '/.ness/users.json';

if (!file_exists($filename_users)) {
    throw new \Error("File '~/.ness/users.json' does not exist !\nMake sure you have copied configuration from 'NessNodeTester/out/config' directory");
}

$filename_payments = posix_getpwuid(getmyuid())['dir'] . '/.ness/payments.json';

if (!file_exists($filename_payments)) {
    throw new \Error("File '~/.ness/payments.json' does not exist !\nMake sure you have copied configuration from 'NessNodeTester/out/config' directory");
}

$ness_config = json_decode(file_get_contents($filename_ness), true);
$ness_config['users_addr_file'] = $filename_users;
$ness_config['users_payments_file'] = $filename_payments;

return $ness_config;