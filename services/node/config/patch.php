<?php

return [
    'info' => ['\services\node\controllers\Node', 'info'],
    'nodes' => ['\services\node\controllers\Node', 'nodes'],
    'services' => ['\services\node\controllers\Node', 'services'],
    'man' => ['\services\node\controllers\Node', 'man'],
    'pub' => ['\services\node\controllers\Node', 'pub'],
    'verify' => ['\services\node\controllers\Node', 'verify'],
    'test/auth' => ['\services\node\controllers\Node', 'testAuthTwoWay'],
    'test/auth/([^/]+)/([^/]+)' => ['\services\node\controllers\Node', 'testAuthId'],
    '' => ['\services\node\controllers\Node', 'man'],
    'get-address/([^/]+)/([^/]+)' => ['\services\node\controllers\Node', 'getAddress'],
    'balance/([^/]+)/([^/]+)' => ['\services\node\controllers\Node', 'balance'],
    'userinfo/([^/]+)/([^/]+)' => ['\services\node\controllers\Node', 'userinfo'],
    'withdraw' => ['\services\node\controllers\Node', 'Withdraw']
];