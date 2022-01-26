<?php
namespace modules\ness\interfaces;

use DateTime;

/**
 * Storage for ness node
 *
 * @author Aleksej Sokolov <aleksej000@gmail.com>,<chosenone111@protonmail.com>
 */
interface Storage {
    public function readUsers(): array;
    public function readUser(string $username): array;
    public function writeUser(string $username, string $address = '', int $counter = 0, int $random_hours = 0);
    public function readPayments(): array;
    public function writePayment(string $username, string $date, int $hours, int $coin_hours_payed, string $txid);
}