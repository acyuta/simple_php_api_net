<?php

require_once __DIR__ . "/../lib.php";
require_once __DIR__ . "/../logic.php";
$config = include __DIR__ . "/../config.php";

$json = [
    "appid" => "4",
    "time" => "1435093393",
    "type" => "GETJOB",
    "customField" => [],
];
$json_string = json_encode($json,true);
$data = openssl_encrypt($json_string, $config['cipherTypeDecryption'],
    $config["keyDecryption"], true);
$_SERVER["REMOTE_ADDR"] = "37.49.216.74";
echo print_r(handle($data), true);
