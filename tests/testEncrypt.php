<?php
require_once __DIR__ . "/../lib.php";
$config = include __DIR__ . "/../config.php";
$data = [
    "result" => "DONE",
    "tasks" => "NOTASKS",
];

$json = json_encode($data);
$json = "{
    \"result\": \"DONE\",
    \"tasks\": \"NOTASKS\"
}";
echo bin2hex(encrypt($json,$config));
