<?php
function encrypt($string, $config)
{
    $string = (is_string($string)) ? $string : strval($string);
    return openssl_encrypt($string, $config["cipherTypeEncryption"],
        $config["keyEncryption"], true, pack('H*', $config["intlVectorEncryption"]));
}

function decrypt($string, $config)
{
    $string = (is_string($string)) ? $string : strval($string);
    return openssl_decrypt($string, $config['cipherTypeDecryption'],
        $config["keyDecryption"], true);
}

function getDb($config)
{
    $options = (isset($config["db"]["options"])) ? $config["db"]["options"] : [];
    return new PDO($config["db"]["dsn"],
        $config["db"]["username"],
        $config["db"]["password"],
        $options);
}

function execSql($db,$sql,$title,$success_result = 0)
{
    echo "Execute ". $title ."........";
    /** @var PDO $db */
    $result = $db->exec($sql);
    if ($result === false) {
        $db->rollBack();
        echo "error\nError stack: " . print_r($db->errorInfo(),true) . "\n";
        die;
    }
    echo "success\n";
}

function checkDbAccess($config) {
    if (!isset($config['db'])) die("No DB Configuration");
    if (!isset($config['db']['username'])) die("unset DB username");
    if (!isset($config['db']['dsn'])) die("unset DB dsn");
    if (!isset($config['db']['password'])) die("unset DB password");
    return true;
}