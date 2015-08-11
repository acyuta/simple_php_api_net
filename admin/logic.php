<?php
require_once __DIR__ . "/alib.php";
require_once __DIR__ . "/../lib.php";

error_reporting(E_ALL);

// Добавлять в отчет все PHP ошибки
error_reporting(-1);

// То же, что и error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);

if (!CAdmin::checkLogin() && $_SERVER['PHP_SELF'] !== '/admin/login.php')
    header('Location: /admin/login.php');


class CAdmin {

    const LOGIN_COOKIE = 'ci';

    /** @var PDO $_db */
    private static $_db;
    private static $_config;

    static function init(){
        static::$_config = include_once __DIR__ . "/../config.php";
        static::$_db = getDb(static::$_config);
    }

    /** @return PDO */
    static function getDb() {
        return static::$_db;
    }

    static function setCookie($name, $value, $time = null) {
        if ($time == null || !is_int($time))
            // Day cookie
            $time = (isset(static::$_config['cookie_time'])) ? static::$_config['cookie_time'] : time()+60*60*24;
        setcookie($name,$value,$time,"/admin/");
    }

    static function checkLogin() {
        return isset($_COOKIE[static::LOGIN_COOKIE]);
    }

    static function login($username,$password) {
        if (!is_string($username) || !is_string($password))
            return false;
        $sql = "SELECT * FROM users WHERE name = :username;";
        $db = static::getDb();
        $s = $db->prepare($sql);
        $s->execute([":username" => $username]);
        $user = $s->fetch(PDO::FETCH_ASSOC);
        if ($user != null && $user !== false && verifyPassword($password,$user["password"]))
        {
            static::setCookie(static::LOGIN_COOKIE,$user["auth_key"]);
            static::updateAuthKey($user["id"]);
            return true;
        } else return false;
    }

    private static function updateAuthKey($id)
    {
        // need to do
    }
}

CAdmin::init();


