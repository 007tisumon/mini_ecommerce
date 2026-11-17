<?php

if (!defined('PROJECT_ROOT')) {
    define('PROJECT_ROOT', rtrim($_SERVER['DOCUMENT_ROOT'] ?? 'C:/xampp_lite_8_5/www', '/\\') . '/mini_ecommerce');
}

function database()
{
    static $connection;

    if (!$connection) {
        $connection = mysqli_connect('localhost', 'root', '', 'bloom_basket');
        mysqli_set_charset($connection, 'utf8mb4');
    }

    return $connection;
}
