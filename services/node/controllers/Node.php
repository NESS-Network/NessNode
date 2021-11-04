<?php
namespace services\node\controllers;

use modules\emer\exceptions\EConnectionError;
use modules\crypto\Crypto;
use internals\lib\Output;
use services\node\models\Node as NodeModel;

class Node {
    public function info() {
        $node = new NodeModel();
        
        try {
            Output::info($node->getInfo());
        } catch (EConnectionError $exception) {
            Output::error('Can not connect to emercoin');
        }
    }

    public function nodes() {
        $node = new NodeModel();
        
        try {
            Output::data($node->listNodes());
        } catch (EConnectionError $exception) {
            Output::error('Can not connect to emercoin');
        }
    }

    public function services() {
        $services = require __DIR__ . '/../../../etc/services.php';
        Output::data($services);
    }

    public function testAuthId(string $username, $id) {
        var_dump($username, $id);
        // ...
        echo 'testAuthId';
    }

    public function testAuthTwoWay() {
        // ...
        echo 'testAuthTwoWay';
    }

    public function man() {
        Output::text(file_get_contents(__DIR__ . '/../../../etc/manual.txt'));
    }
}
