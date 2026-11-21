<?php
session_start();
require "functions.php";

role_check('admin');

require '../database/config.php';
$conn->set_charset('utf8mb4');

require   'partials/head.php';
require  'partials/left_bar.php';
require  'partials/header.php';



require 'partials/dashboard.php';
require 'partials/footer.php';
require   'partials/scripts.php';

