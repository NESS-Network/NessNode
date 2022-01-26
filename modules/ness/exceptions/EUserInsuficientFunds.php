<?php
namespace modules\ness\exceptions;

use \Throwable;

class EUserInsuficientFunds extends \Exception {
    public function __construct(string $username, float $coins, int $hours, float $need_coins, int $need_hours, int $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            "User '$username' do not have enough funds(coins:$coins AND hours:$hours) at least (coins:$need_coins + 0.001 AND hours:$need_hours) needed" , 
            $code, 
            $previous
        );
    }
}