<?php
namespace modules\ness\exceptions;

use \Throwable;

class EMasterUserDontExist extends \Exception {
    public function __construct(string $MasterUserName, int $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            "Master user '$MasterUserName' does bot exist in 'users_addr.json' file" , 
            $code, 
            $previous
        );
    }
}