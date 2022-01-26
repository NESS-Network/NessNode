<?php
namespace services\node\models;

use modules\emer\Emer;
use modules\worm\Worm;

class Node {
    private array $config;

    public function __construct() {
        $this->config = require '../config/node.php';
    }

    public function getInfo(): Array {
        $emer = new Emer();
        $services = require __DIR__ . '/../../../etc/services.php';
        $info = $services['node'];
        $info['emercoin'] = $emer->info();

        return $info;
    }

    public function listNodes(): Array {
        $emer = new Emer();
        $result = [];
        
        $nodes = $emer->listNodes();

        foreach ($nodes as $name => $value) {
            if (Worm::isNode($value)) {
                $result[$name] = Worm::parseNode($value);
            }
        }

        return $result;
    }
}