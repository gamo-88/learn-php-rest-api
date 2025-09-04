<?php

declare(strict_types=1);

spl_autoload_register(function ($class) {
    require __DIR__ . "/src/$class.php";
});

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

$database = new Database("localhost", "PDO_LEARN", "gamo", "gamo1234");
$database->getConnection();

$controller = new ProductController();

$controller->processRequest($_SERVER["REQUEST_METHOD"], $id);