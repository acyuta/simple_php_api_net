<?php
function encrypt($string, $config)
{
    return openssl_encrypt($string, $config["cipherTypeEncryption"],
        $config["keyEncryption"], true, pack('H*',$config["intlVectorEncryption"]));
}

function decrypt($string, $config)
{
    return openssl_decrypt($string,$config['cipherTypeDecryption'],
        $config["keyDecryption"],true);
}