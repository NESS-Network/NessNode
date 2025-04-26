<?php
require 'utils/autoload.php';
require 'utils/format.php';
require_once __DIR__ . '/../services/files/vendor/autoload.php';

// $data = file_get_contents("/dev/urandom", false, null, 0, 10000);
$data = "eb5606b44d2ef77af3753904a8139450-17-millions.mp4";

echo hash('sha3-256', $data, false);
echo "\n";
echo hash('sha3-256', $data, true);
echo "\n";
echo \Base32\Base32::encode(hash('sha3-256', $data, true));
echo "\n";