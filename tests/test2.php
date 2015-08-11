<?php

require __DIR__ . "/../lib.php";
require __DIR__ . "/../logic.php";
$config = include __DIR__ . "/../config.php";
function curlPost($url, $file)
{
    $ch = curl_init();
    if (!is_resource($ch)) return false;
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $file);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

$filename = "data.bin";
$json = [
    "appid" => "4",
    "time" => "1435093393",
    "type" => "GETJOB",
    "customField" => [],
];
$json_string = json_encode($json, true);
$data = openssl_encrypt($json_string, $config['cipherTypeDecryption'],
    $config["keyDecryption"], true);
$url = "http://www.vd27-test.com/fw/api.php";

echo var_export(curlPost($url, encrypt($data, $config)),true);
