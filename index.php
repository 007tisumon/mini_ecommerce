<?php

const BASE_PATH = __DIR__ ;

echo BASE_PATH;

// $url = parse_url($_SERVER['REQUEST_URI'])['path'];

// $routes = [
//     '/'=> 'index.php',
//     '/admin'=> 'admin/index.html'
// ];

// echo BASE_PATH;

// function route($routes){
//     foreach($routes as $route){
//         require();
//     }
// }


require __DIR__ . '/admin/index.html';

// echo "front end Home Page";
// dd($url);
