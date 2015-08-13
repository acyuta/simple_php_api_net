<?php
require_once __DIR__ . "/logic.php";
$data = file_get_contents("php://input");

try {
    $response = handle($data);
} catch (Exception $e) {
    $data = time() . '('. $_SERVER['REMOTE_ADDR']. ') '.var_export($e,true);
    file_put_contents(__DIR__ .'/errors.log',$data,FILE_APPEND);
    die;
}

if ($response !== false)
    echo $response;
else
    die;
