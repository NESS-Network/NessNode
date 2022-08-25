<?php
 
return [
    'seed/([^/]+)/([^/]+)' => ['\services\prng\controllers\Prng', 'seed'],
    'seedb/([^/]+)/([^/]+)' => ['\services\prng\controllers\Prng', 'seedb'],
    'numbers/([^/]+)/([^/]+)' => ['\services\prng\controllers\Prng', 'numbers'],
    'numbersb/([^/]+)/([^/]+)' => ['\services\prng\controllers\Prng', 'numbersb'],
    'i256/([^/]+)/([^/]+)' => ['\services\prng\controllers\Prng', 'i256'],
    'h256/([^/]+)/([^/]+)' => ['\services\prng\controllers\Prng', 'h256']
];