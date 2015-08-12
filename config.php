<?php

return [
    "keyDecryption" => "92834682956035407921965484329111",
    "cipherTypeDecryption" => "aes-256-ecb",
    "keyEncryption" => "18836682852133447678964414319238",
    "cipherTypeEncryption" => "aes-256-cbc",
    "intlVectorEncryption" => "92834682956035407921965484329111",
    "db" => [
        "dsn" => "mysql:host=localhost;dbname=temp",
        "username" => 'root',
        "password" => 123,
        "options" => [],
    ],
    'geoipPath' => '/usr/share/GeoIP/',
    "record_connection" => true, // record information about each request (ip, country, etc)

];