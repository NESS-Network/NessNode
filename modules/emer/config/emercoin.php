<?php
 
// return [
//     "host" => "localhost",
//     "port" => 8332,
//     "user" => "user",
//     "password" => "hpe74xjkd"
// ];

return json_decode(file_get_contents(posix_getpwuid(getmyuid())['dir'] . '/.ness/emer.json'), true);