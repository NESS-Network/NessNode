<?php
require_once '../internals/Launcher.php';
require_once 'vendor/autoload.php';

use internals\Launcher;

$launcher = Launcher::getInstance();
$launcher->runControllers(require 'config/patch.php');