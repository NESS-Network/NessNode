<?php

// return [
//     'files' => [
//         'seed' => '/tmp/seed.txt',
//         'seedb' => '/tmp/seed-big.txt',
//         'numbers' => '/tmp/numbers.json',
//         'numbersb' => '/tmp/numbers-big.json',
//     ]
// ];

return json_decode(file_get_contents(posix_getpwuid(getmyuid())['dir'] . '/.ness/prng.json'), true);