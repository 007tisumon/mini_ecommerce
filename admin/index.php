<?php
session_start();
require "functions.php";

role_check('admin');



require   'partials/head.php';
require  'partials/left_bar.php';
require  'partials/header.php';



require 'partials/dashboard.php';
require 'partials/footer.php';
require   'partials/scripts.php';

