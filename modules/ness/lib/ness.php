<?php
namespace modules\ness\lib;


/**
 * Ness lib module
 *
 * @author Aleksej Sokolov <aleksej000@gmail.com>,<chosenone111@protonmail.com>
 */
class ness {
  public static $host = '';
  public static $port = '';
  public static $wallet_id = '';
  public static $password = '';

  public static $fee = 0.1;

  public static $output = [];

  public function createAddr(): array {
    $responce = file_get_contents("http://" . self::$host . ":" . self::$port . "/api/v1/csrf");

    if (empty($responce)) {
      throw new \Exception("Privateness daemon is not running");
    }

    $responce = json_decode($responce, true);
    $token = $responce["csrf_token"];

    $fields = [
      'id' => self::$wallet_id,
      'num' => 1,
      'password' => self::$password
    ];

    $ch = curl_init("http://" . self::$host . ":" . self::$port . "/api/v1/wallet/newAddress");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-CSRF-Token: '.$token));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

    self::$output = curl_exec($ch);

    if (empty(self::$output)) {
      throw new \Exception("Privateness daemon is not running");
    }

    return json_decode(self::$output, true);
  }

  public function getBalance(string $addr): array {
    // var_dump($addr);
    self::$output = file_get_contents("http://" . self::$host . ":" . self::$port . "/api/v1/balance?addrs=" . $addr);
    // var_dump(self::$output);

    if (empty(self::$output)) {
      throw new \Exception("Privateness daemon is not running");
    }

    return json_decode(self::$output, true);
  }

  public function transactions(string $addr): array {
    // var_dump($addr);
    self::$output = file_get_contents("http://" . self::$host . ":" . self::$port . "/api/v1/transactions?addrs=" . $addr . "confirmed=1");
    // var_dump(self::$output);

    if (empty(self::$output)) {
      throw new \Exception("Privateness daemon is not running");
    }

    return json_decode(self::$output, true);
  }

  public function getFee(int $hours): int {
    return (int) round($hours * self::$fee);
  }

  public function send(string $from_addr, $to_addr, float $coins, int $hours) {
    $responce = file_get_contents("http://" . self::$host . ":" . self::$port . "/api/v1/csrf");

    if (empty(self::$output)) {
      throw new \Exception("Privateness daemon is not running");
    }

    $responce = json_decode($responce, true);
    $token = $responce["csrf_token"];
    $wallet_id = self::$wallet_id;
    $password = self::$password;

    $body = <<<BODY
    {
        "hours_selection": {
            "type": "manual"
        },
        "wallet_id": "$wallet_id",
        "password": "$password",
        "addresses": ["$from_addr"],
        "to": [{
            "address": "$to_addr",
            "coins": "$coins",
            "hours": "$hours"
        }]
    }
BODY;

    try {

      $ch = curl_init("http://" . self::$host . ":" . self::$port . "/api/v1/wallet/transaction");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'X-CSRF-Token: '.$token));
      curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

      self::$output = curl_exec($ch);
      $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

      if (200 !== $httpcode) {
        $msg = explode(' - ', self::$output, 2);

        if(2 === count($msg)) {
          $msg = $msg[1];
        } else {
          $msg = $msg[0];
        }

        throw new \Exception($msg);
      }

      $json_output = json_decode(self::$output, true);
      $encoded_transaction = $json_output['encoded_transaction'];

      $body = '{"rawtx": "' . $encoded_transaction . '"}';

      // var_dump($output); 
      // die();

      $ch = curl_init("http://" . self::$host . ":" . self::$port . "/api/v1/injectTransaction");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'X-CSRF-Token: '.$token));
      curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
      self::$output = curl_exec($ch);

    } catch (\Throwable $th) {
      self::$output = $th->getMessage();
      return false;
    }

    return true;
  }
}
