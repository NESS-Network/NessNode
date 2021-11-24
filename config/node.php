<?php
 
// return [
//     'nonce' => 'wlqEIW0GRGC0QWWxlEfvAw==',
//     'private' => 'njtQRKK4O6O7BhlAW7qwi+Qak8g1gOXESTAcEUpj2GM=',
//     'verify' => 'b0HnLhadnT1RBEX/BzNU7Ma4/HQDty4Qy6UxPhYvXzk=',
//     'public' => 'IkiI89Z/Bw+2mzQTRz6HYPb6YRMQxcOE6RuAf2A/knI=',
//     'url' => 'http://node.zxc'
// ];

return json_decode(file_get_contents(posix_getpwuid(getmyuid())['dir'] . '/.ness/node.json'), true);