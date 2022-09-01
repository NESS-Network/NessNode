<?php
namespace services\files\exceptions;

use \Throwable;

class ECantCreatePath extends \Exception {
    public function __construct(string $filepath, int $code = 0, Throwable $previous = null)
    {
        parent::__construct("Can't create filepath '$filepath'", $code, $previous);
    }
}
