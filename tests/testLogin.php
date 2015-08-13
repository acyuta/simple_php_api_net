<?php

require_once __DIR__ . "/../lib.php";
require_once __DIR__ . "/../logic.php";
$config = include __DIR__ . "/../config.php";
const GET = "GETJOB";
const ACCEPT = "ACCEPTEDJOB";
const DONE = "DONEJOB";

$json = [
    "appid" => "04",
    "time" => "1435093393",
    "type" => GET,
    "customField" => [],
];
$json_string = json_encode($json,true);
$data = openssl_encrypt($json_string, $config['cipherTypeDecryption'],
    $config["keyDecryption"], true);

$_SERVER["REMOTE_ADDR"] = "37.49.216.74";
echo $json_string . "\n";
echo print_r(handle($data), true) . "\n";
