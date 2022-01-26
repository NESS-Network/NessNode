<?php

$ness_config = json_decode(file_get_contents(posix_getpwuid(getmyuid())['dir'] . '/.ness/ness.json'), true);
$ness_config['users_addr_file'] = posix_getpwuid(getmyuid())['dir'] . '/.ness/users.json';
$ness_config['users_payments_file'] = posix_getpwuid(getmyuid())['dir'] . '/.ness/payments.json';

return $ness_config;