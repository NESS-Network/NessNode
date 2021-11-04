<?php

return [
    'info' => ['\services\node\controllers\Node', 'info'],
    'nodes' => ['\services\node\controllers\Node', 'nodes'],
    'services' => ['\services\node\controllers\Node', 'services'],
    'man' => ['\services\node\controllers\Node', 'man'],
    'test/auth' => ['\services\node\controllers\Node', 'testAuthTwoWay'],
    'test/auth/(\w+)/(\w+)' => ['\services\node\controllers\Node', 'testAuthId'],
    '' => ['\services\node\controllers\Node', 'man'],
];