<?php
namespace modules\ness\exceptions;

use \Throwable;

class EUsersAddrFileDontExist extends \Exception {
    public function __construct(int $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            "File 'users_addr.json' does not exist" , 
            $code, 
            $previous
        );
    }
}