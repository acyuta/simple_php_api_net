<?php

require_once __DIR__ . "/logic.php";

CAdmin::logout();
header("Location: /admin/login.php");
?>