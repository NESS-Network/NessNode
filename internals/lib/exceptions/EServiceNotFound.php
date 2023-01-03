<?php
namespace internals\lib\exceptions;

class EServiceNotFound extends \Exception {
    public string $service = '';

    public function __construct(string $service, string $run_filename, int $code = 0, ?\Throwable $previous = null) {
        $this->service = $service;
        parent::__construct("Service '$service' runfile '$run_filename' not found.", $code, $previous);
    }
}