<?php

namespace modules\ness;

use modules\ness\Privateness;
use modules\ness\lib\StorageJson;

class Creator {
    public static function Privateness(): Privateness
    {
        $json = new StorageJson();
        return new Privateness($json);
    }
}