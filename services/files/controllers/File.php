<?php

namespace services\file\controllers;

use modules\emer\exceptions\EConnectionError;
use modules\crypto\Crypto;
use internals\lib\Output;
use Base32\Base32;
use modules\ness\lib\ness;
use modules\ness\Privateness;
use modules\ness\lib\StorageJson;

class File
{
    public function man()
    {
        Output::text(file_get_contents(__DIR__ . '/../../../etc/manual.txt'));
    }
}
