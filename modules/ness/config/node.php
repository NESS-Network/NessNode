<?php
return json_decode(file_get_contents(posix_getpwuid(getmyuid())['dir'] . '/.ness/node.json'), true);