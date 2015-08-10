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