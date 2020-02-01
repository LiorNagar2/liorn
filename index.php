<?php

include_once (__DIR__ . '/views/includes/header.php');

$request = $_SERVER['REQUEST_URI'];

switch ($request) {
    case '/' || '' :
        require __DIR__ . '/views/index.php';
        break;
    case '/about' :
        require __DIR__ . '/views/about.php';
        break;
    default:
        http_response_code(404);
        require __DIR__ . '/views/404.php';
        break;
}

include_once (__DIR__ . '/views/includes/footer.php');