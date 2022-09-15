<?php

return [
    '' => ['\services\files\controllers\File', 'man'],
    'quota' => ['\services\files\controllers\File', 'quota'],
    'list' => ['\services\files\controllers\File', 'list'],
    'fileinfo' => ['\services\files\controllers\File', 'fileinfo'],
    'download/([^/]+)/([^/]+)/([^/]+)' => ['\services\files\controllers\File', 'download'],
    'touch' => ['\services\files\controllers\File', 'touch'],
    'remove' => ['\services\files\controllers\File', 'remove'],
    'append/([^/]+)/([^/]+)/([^/]+)' => ['\services\files\controllers\File', 'append'],
    'pub/([^/-]+)-([^/-]+)-([^/-]+)' => ['\services\files\controllers\File', 'pub'],
];