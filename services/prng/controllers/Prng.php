<?php
namespace Services\prng\controllers;

use internals\lib\Output;
use services\prng\exceptions\EFileNotFound;
use services\prng\models\Prng as PrngModel;

/**
 *      !!! Warning !!!
 *      Change systemd configuration for apache
 *      in /lib/systemd/apache2.service 
 *      or /usr/lib/systemd/system/httpd.service (Arch)
 *      the *** PrivateTmp=false ***
 */

class Prng {
    public function seed() {
        $prng = new PrngModel();

        try {
            Output::data(['seed' => $prng->seed()]);
        } catch (EFileNotFound $exception) {
            Output::error($exception->getMessage());
        }
    }
    
    public function seedb() {
        $prng = new PrngModel();

        try {
            Output::data(['seed' => $prng->seedb()]);
        } catch (EFileNotFound $exception) {
            Output::error($exception->getMessage());
        }
    }

    public function numbers() {
        $prng = new PrngModel();

        try {
            Output::data(['numbers' => $prng->numbers()]);
        } catch (EFileNotFound $exception) {
            Output::error($exception->getMessage());
        }
    }

    public function numbersb() {
        $prng = new PrngModel();

        try {
            Output::data(['numbers' => $prng->numbersb()]);
        } catch (EFileNotFound $exception) {
            Output::error($exception->getMessage());
        }
    }
}