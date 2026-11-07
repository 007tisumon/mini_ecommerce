<?php

$conn = mysqli_connect("localhost","root","","bloom_basket");

if (mysqli_connect_errno()) {
    printf("", mysqli_connect_error());
 }
// else{
//     // echo "connect successs";
// }