<?php
namespace modules\ness\lib;

use modules\ness\interfaces\Storage;
use modules\ness\exceptions\ENessDirNotWritable;

class StorageJson implements Storage {

    private array $config;
    private string $users_addr_file;
    private string $users_payments_file;

    public function __construct() 
    {
        $this->config = require __DIR__ . '/../../../config/ness.php';
        $this->users_addr_file = $this->config['users_addr_file'];
        $this->users_payments_file = $this->config['users_payments_file'];
    }

    public function readUsers(): array 
    {
        if (file_exists($this->users_addr_file)) {
            $data = json_decode(file_get_contents($this->users_addr_file), true);
            
            if (empty($data))  {
                throw new \Error("Users file not decoded");
            }

            return $data;
        } else {
            return [];
        }
    }

    public function readUser(string $username): array 
    {
        $users = $this->readUsers();
        if (!empty($users)) {
            if (isset($users[$username])) {
                return $users[$username];
            } else {
                return [];
            }
        } else {
            return [];
        }
    }


    public function findUser(string $shadowname): array
    {
        $users = $this->readUsers();

        if (!empty($users)) {
            foreach ($users as $user) {
                if (isset($user['shadowname']) && ($user['shadowname'] === $shadowname)) {
                    return $user;
                }
            }
        }
            
        return [];
    }

    public function writeUser(string $username, string $address = '', int $counter = 0, int $random_hours = 0, string $shadowname = '') 
    {
        $users = $this->readUsers();

        if (!empty($address)) {
            $users[$username]['addr'] = $address;
        }

        if (is_int($counter)) {
            $users[$username]['counter'] = $counter;
        }

        if (!empty($random_hours)) {
            $users[$username]['random_hours'] = $random_hours;
        }

        if (!empty($shadowname)) {
            $users[$username]['shadowname'] = $shadowname;
        }

        try {
            file_put_contents($this->users_addr_file, json_encode($users));
            chmod($this->users_addr_file, 0666);
        } catch (\Throwable $th) {
            throw new ENessDirNotWritable();
        }
    }

    public function readPayments(): array 
    {
        if (file_exists($this->users_payments_file)) {
            return json_decode(file_get_contents($this->users_payments_file), true);
        } else {
            return [];
        }
    }

    public function writePayment(string $username, string $date, int $hours, int $coin_hours_payed, string $txid) 
    {
        $payments = $this->readPayments();

        $payments[$username] = [
            'date' => $date, 
            'hours' => $hours, 
            'coin_hours_payed' => $coin_hours_payed, 
            'txid' => $txid
        ];

        try {
            file_put_contents($this->users_payments_file, json_encode($payments));
        } catch (\Throwable $th) {
            throw new ENessDirNotWritable();
        }
    }
}