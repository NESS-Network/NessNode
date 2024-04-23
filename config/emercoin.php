<?php
$hdf = __DIR__ . '/../homedir';

if (file_exists($hdf)) {
    $homedir = file_get_contents($hdf);
} else {
    $homedir = posix_getpwuid(getmyuid())['dir'];
}
 
$filename = $homedir . '/.ness/emer.json';

if (!file_exists($filename)) {
    throw new \Error("File '~/.ness/emer.json' does not exist !\nMake sure you have copied configuration from 'NessNodeTester/out/config' directory");
}

return json_decode(file_get_contents($filename), true);