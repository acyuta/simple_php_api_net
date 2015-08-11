<?php

require_once __DIR__ . "/logic.php";
if (isset($_POST["login"]) && isset($_POST["password"])) {
    $username = post("login");
    $password = post("password");
    $r = CAdmin::login($username,$password);
    if ($r) header("Location: /admin/index.php");
}

if (CAdmin::checkLogin())
    header("Location: /admin/index.php");
?>

<!DOCTYPE html>
<html lang="en">
<meta charset="UTF-8">
<head>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/bootstrap-theme.min.css">
</head>
<body>
<div class="container">

    <div class="jumbotron">
        <h3 class="center-block">Login</h3>
        <form method="post" accept-charset="UTF-8" enctype="application/x-www-form-urlencoded" autocomplete="off">
            <div class="form-group form-group-sm">
                <input type="text" class="form-control input-sm" id="inputLogin" placeholder="Login" name="login">
            </div>
            <div class="form-group form-group-sm">
                <input type="password" class="form-control input-sm" id="inputPassword" placeholder="Password" name="password">
            </div>
            <button type="submit" class="btn btn-default">Enter</button>
        </form>
    </div>

</div><!-- /.container -->
</body>
</html>