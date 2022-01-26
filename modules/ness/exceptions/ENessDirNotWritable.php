<?php
namespace modules\ness\exceptions;

use \Throwable;

class ENessDirNotWritable extends \Exception {
    public function __construct(int $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            "Directory '~/.ness' not writable, check directory rights" , 
            $code, 
            $previous
        );
    }
}