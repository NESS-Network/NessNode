<?php
ini_set('display_errors', 'yes');
error_reporting(E_ALL);

require_once '../internals/Launcher.php';
require_once '../internals/lib/PathResolverHttpGet.php';

use internals\Launcher;
use internals\lib\PathResolverHttpGet;

$resolver = new PathResolverHttpGet();
$launcher = Launcher::getInstance($resolver);
$launcher->runServices();
