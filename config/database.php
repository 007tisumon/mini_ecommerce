<?php

function database()
{
    static $connection;

    if (!$connection) {
        $connection = mysqli_connect('localhost', 'root', '', 'bloom_basket');
        mysqli_set_charset($connection, 'utf8mb4');
    }

    return $connection;
}
