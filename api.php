<?php
require_once __DIR__ . "/logic.php";
$data = file_get_contents("php://input");

$response = handle($data);

if ($response !== false)
    echo $response;
else
    die;
