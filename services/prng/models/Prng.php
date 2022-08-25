<?php
namespace Services\prng\models;

use services\prng\exceptions\EFileNotFound;

class Prng {

    private string $seed;
    private string $seedb;
    private string $numbers;
    private string $numbersb;
    private string $numbersi256;
    private string $numbersh256;

    public function __construct() {
        $config = require __DIR__ . '/../config/prng.php';
        var_dump($config);
        $this->seed = $config['seed'];
        $this->seedb = $config['seed-big'];
        $this->numbers = $config['numbers'];
        $this->numbersb = $config['numbers-big'];
        $this->numbersi256 = $config['numbers-i256'];
        $this->numbersh256 = $config['numbers-h256'];
    }

    public function seed(): string {
        if (!file_exists($this->seed)) {
            throw new EFileNotFound($this->seed);
        }

        return file_get_contents($this->seed);
    }
    
    public function seedb(): string  {
        if (!file_exists($this->seedb)) {
            throw new EFileNotFound($this->seedb);
        }

        return file_get_contents($this->seedb);
    }

    public function numbers(): array {
        if (!file_exists($this->numbers)) {
            throw new EFileNotFound($this->numbers);
        }

        return json_decode(file_get_contents($this->numbers));
    }

    public function numbersb(): array {
        if (!file_exists( $this->numbersb)) {
            throw new EFileNotFound( $this->numbersb);
        }

        return json_decode(file_get_contents( $this->numbersb));
    }

    public function numbers256i(): array {
        if (!file_exists( $this->numbersi256)) {
            throw new EFileNotFound( $this->numbersi256);
        }

        return json_decode(file_get_contents( $this->numbersi256), false, 512, JSON_BIGINT_AS_STRING);
    }

    public function numbers256h(): array {
        if (!file_exists( $this->numbersh256)) {
            throw new EFileNotFound( $this->numbersh256);
        }

        return json_decode(file_get_contents( $this->numbersh256));
    }
}