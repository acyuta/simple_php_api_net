<?php
require_once __DIR__."/../logic.php";
$config = include __DIR__ . "/../config.php";

echo print_r(getJob($config,2,2123123123));
