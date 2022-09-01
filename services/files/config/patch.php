<?php

return [
    '' => ['\services\files\controllers\Node', 'man'],
    'man' => ['\services\files\controllers\File', 'man'],
    'quota/([^/]+)/([^/]+)' => ['\services\files\controllers\File', 'quota'],
    'list/([^/]+)/([^/]+)' => ['\services\files\controllers\File', 'list'],
];