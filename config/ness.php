<?php
$hdf = __DIR__ . '/../homedir';

if (file_exists($hdf)) {
    $homedir = file_get_contents($hdf);
} else {
    $homedir = posix_getpwuid(getmyuid())['dir'];
}
 
$filename_ness = $homedir . '/.ness/ness.json';
$datadir = $homedir . '/.ness/data';
$logdir = $homedir . '/.ness/log';
$filename_users = $datadir . '/users.json';
$filename_payments = $datadir . '/payments.json';

if (!file_exists($filename_ness)) {
    throw new \Error("File '~/.ness/ness.json' does not exist !");
}

if (!file_exists($datadir)) {
    throw new \Error("Directory '~/.ness/data' does not exist !");
}

if (!is_writeable($datadir)) {
    throw new \Error("Directory '~/.ness/data' is not writable !");
}

if (!file_exists($logdir)) {
    throw new \Error("File '~/.ness/log' does not exist !");
}

if (!is_writeable($logdir)) {
    throw new \Error("Directory '~/.ness/log' is not writable !");
}

$ness_config = json_decode(file_get_contents($filename_ness), true);
$ness_config['users_addr_file'] = $filename_users;
$ness_config['users_payments_file'] = $filename_payments;

return $ness_config;