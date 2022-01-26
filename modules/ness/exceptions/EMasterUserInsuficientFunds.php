<?php
namespace modules\ness\exceptions;

use \Throwable;

class EMasterUserInsuficientFunds extends \Exception {
    public function __construct(string $MasterUserName, float $coins, int $hours, int $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            "Master user '$MasterUserName' do not have enough funds coins:$coins hours:$hours, at least 0.002 coins and 2 hours needed" , 
            $code, 
            $previous
        );
    }
}