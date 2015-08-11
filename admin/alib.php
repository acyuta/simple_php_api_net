<?php

function hashPassword($password) {
    $result = password_hash($password,PASSWORD_BCRYPT);
    if (is_string($result))
        return $result;
    else return false;
}

function verifyPassword($password,$hash)
{
    return password_verify($password,$hash);
}

function generateRandomString($length = 32)
{
    if ($length <= 0)
        return "";
    $chars = "#!@qwertyuiopasdfghjklzxcvbnm123456789"; //35 chars
    $result = "";
    for($p = 0; $p < $length; $p++)
        $result .= $chars[mt_rand(0,34)];
    return $result;
}

function post($name) {
    $var = filter_input(INPUT_POST,$name,FILTER_SANITIZE_STRING);
    if ($var === false) return null;
    return $var;
}

function isAjax() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
    && !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
    && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}