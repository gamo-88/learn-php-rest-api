<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php'; 

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
spl_autoload_register(function ($class) {
    require __DIR__ . "/src/$class.php";
});

set_error_handler("ErrorHandler::handleError");
set_exception_handler("ErrorHandler::handleException");

header("Content-type: application/json; charset: UTF-8");

$parts = explode("/", $_SERVER["REQUEST_URI"]);




if ($parts[1] != "products") {
    http_response_code(404);
    exit;
}


//products/123
//products
$id = $parts[2] ?? NULL;

$database = new Database(
    $_ENV['DB_HOST'],
    $_ENV['DB_NAME'],
    $_ENV['DB_USER'],
    $_ENV['DB_PASS']
);

$gateway = new ProductGateway($database);
$imageServices = new ImageServices();

$controller = new ProductController($gateway, $imageServices);

$controller->processRequest($_SERVER["REQUEST_METHOD"], $id);
