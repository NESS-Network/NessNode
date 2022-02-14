<?php

$filename = posix_getpwuid(getmyuid())['dir'] . '/.ness/node.json';

if (!file_exists($filename)) {
    throw new \Error("File '~/.ness/node.json' does not exist !\nMake sure you have copied configuration from 'NessNodeTester/out/config' directory");
}

return json_decode(file_get_contents($filename), true);