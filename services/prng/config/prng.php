<?php
$hdf = __DIR__ . '/../../../homedir';

if (file_exists($hdf)) {
    $homedir = file_get_contents($hdf);
} else {
    $homedir = posix_getpwuid(getmyuid())['dir'] . "/.ness";
}

return json_decode( file_get_contents($homedir . '/prng.json'), true);