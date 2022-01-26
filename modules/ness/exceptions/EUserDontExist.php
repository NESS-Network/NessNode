<?php
namespace modules\ness\exceptions;

use \Throwable;

class EUserDontExist extends \Exception {
    public function __construct(string $username, int $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            "User '$username' does bot exist in 'users_addr.json' file" , 
            $code, 
            $previous
        );
    }
}