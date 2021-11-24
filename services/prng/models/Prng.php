<?php
namespace Services\prng\models;

use services\prng\exceptions\EFileNotFound;

class Prng {

    private string $seed;
    private string $seedb;
    private string $numbers;
    private string $numbersb;

    public function __construct() {
        $config = require __DIR__ . '/../config/prng.php';
        $files = $config['files'];

        $this->seed = $files['seed'];
        $this->seedb = $files['seed-big'];
        $this->numbers = $files['numbers'];
        $this->numbersb = $files['numbers-big'];
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
}