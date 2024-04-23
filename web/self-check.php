<?php
ob_start();
    require __DIR__ . '/../exec/self-test.php';
$content = ob_get_clean();

$content = str_replace("\n", "<br/>", $content);
$content = str_replace("[32m", "", $content);
$content = str_replace("[32;1m", "", $content);
$content = str_replace("[31m", "", $content);
$content = str_replace("[0m", "", $content);
echo $content;