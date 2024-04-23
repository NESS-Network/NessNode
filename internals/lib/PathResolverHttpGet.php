<?php
namespace internals\lib;

require_once 'interfaces/iPatchResolver.php';

use \internals\lib\interfaces\iPatchResolver;

/**
 * Class returns first GET key name and explodes it by / delimiter
 */
class PathResolverHttpGet implements iPatchResolver {
    static public function getPath(): array {
        $path = ltrim($_GET['path'], "/");
        
        // foreach ($_GET as $key => $value) {
        //     $path = $key;
        //     break;
        // }

        return explode('/', $path);
    }
}