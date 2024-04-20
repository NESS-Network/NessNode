<?php
require 'utils/autoload.php';
require 'utils/format.php';

ini_set('display_errors', 'yes');
error_reporting(E_ALL);

if ($argc == 8) {
    $node_json = $argv[1];
    $wallet_id = $argv[2];
    $password = $argv[3];
    $emc_user = $argv[4];
    $emc_password = $argv[5];
    $user_slots = $argv[6];
    $disk_usage_quota = $argv[7];

    $directory = posix_getpwuid(getmyuid())['dir'] . "/.ness";

    if (!file_exists($node_json)) {
        formatPrintLn(['red', 'b'], "File $node_json does not exist");
        exit(1);
    }

    if (!file_exists($directory)) {
        if (!mkdir($directory)) {
            formatPrintLn(['red', 'b'], "Error creating $directory directory");
            exit(1);
        }
    }

    $node_data = json_decode(file_get_contents($node_json), true);

    // ness
    $filename = $directory . '/ness.json';
    $data = [
        "host" => "localhost",
        "password"  => $password,
        "port"  => 6422,
        "wallet_id"  => $wallet_id
    ];

    file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));
    chmod($filename, 0644);

    // emer
    $filename = $directory . '/emer.json';
    $data = [
        "host" => "localhost",
        "password" => $emc_user,
        "port" => 8332,
        "user" => $emc_password
    ];

    file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));
    chmod($filename, 0644);

    // node
    $filename = $directory . '/node.json';
    $data = [
        "services" => $node_data['services'],
        "delta" => "4",
        "master-user" => $node_data['master-user'],
        "nonce" => $node_data['nonce'],
        "period" => "24",
        "private" => $node_data['keys']['private'],
        "public" => $node_data['keys']['public'],
        "slots" => $user_slots,
        "tariff" => $node_data['tariff'],
        "url" => $node_data['url'],
        "network" => $node_data['network'],
        "verify" => $node_data['keys']['verify']
    ];

    file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));
    chmod($filename, 0644);

    // files
    $filename = $directory . '/files.json';
    $data = [
        "dir" => "storage",
        "quota" => $disk_usage_quota,
        "salt" => base64_encode(random_bytes(32))
    ];

    file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));
    chmod($filename, 0644);

    // prng
    $filename = $directory . '/prng.json';
    $data = [
        "numbers" => "/tmp/numbers.json",
        "numbers-big" => "/tmp/numbers-big.json",
        "numbers-i256" => "/tmp/i256.json",
        "numbers-h256" => "/tmp/h256.json",
        "seed" => "/tmp/seed.txt",
        "seed-big" => "/tmp/seed-big.txt"
    ];

    file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));
    chmod($filename, 0644);

} else {
    formatPrintLn(['green', 'b'], 'Usage: ');
    formatPrintLn(['green'], 'php make-config.php node.key.json wallet_id password emc_user emc_password user_slots disk_usage_quota');
}