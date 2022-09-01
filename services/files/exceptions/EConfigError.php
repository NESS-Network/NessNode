<?php
namespace services\files\exceptions;

use \Throwable;

class EConfigError extends \Exception {
    public function __construct(string $filename, string $param, int $code = 0, Throwable $previous = null)
    {
        parent::__construct("Param '$param' not found in config file '$filename'", $code, $previous);
    }
}
