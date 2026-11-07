<?php 


function role_check(string $role){
    if (!isset($_SESSION['email'])) {
        header('Location: signin.php');
        exit();
    }

    if ($_SESSION['role'] != $role) {
        header("Location: unauthorize.php");
        exit();
    }
}



