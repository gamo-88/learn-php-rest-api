<?php

declare(strict_types=1);

spl_autoload_register(function ($class) {
    require __DIR__ . "/src/$class.php";
});
header("Content-type: application/json; charset: UTF-8");

$parts = explode("/", $_SERVER["REQUEST_URI"]);




if ($parts[1] != "products") {
    http_response_code(404);
    exit;
}


//products/123
//products
$id = $parts[2] ?? NULL;

$controller = new ProductController;

$controller->processRequest($_SERVER["REQUEST_METHOD"], $id);