<?php

class ProductController
{
    public function __construct(private ProductGateway $gateway) {}

    public function processRequest(string $method, ?string $id): void
    {
        if ($id) {
            $this->processRessourceRequest($method, $id);
        } else {
            $this->processCollectionRequest($method);
        }
    }
    private function processRessourceRequest(string $method, string $id): void
    {
        $product = $this->gateway->get($id);
        if (!$product) {
            http_response_code(404);
            echo json_encode(["message" => "Product Not Found"]);
            return;
        }
        switch ($method) {
            case "GET":
                echo json_encode($product);
            case "PATCH":
                $data = (array) json_decode(file_get_contents("php://input"), true);


                $errors = $this->getValidationErrors($data);

                if (!empty($errors)) {
                    http_response_code(422);
                    echo json_encode(["errors" => $errors]);
                    break;
                }


                $rows = $this->gateway->update($product, $data);
                http_response_code(201);
                echo json_encode(
                    [
                        "message" => "Product $id created",
                        "rows" => $rows
                    ]
                );
                break;
        }
    }
    private function processCollectionRequest(string $method): void
    {
        switch ($method) {
            case "GET":
                echo json_encode($this->gateway->getAll());
                break;
            case "POST":
                $data = (array) json_decode(file_get_contents("php://input"), true);


                $errors = $this->getValidationErrors($data);

                if (!empty($errors)) {
                    http_response_code(422);
                    echo json_encode(["errors" => $errors]);
                    break;
                }


                $id = $this->gateway->create($data);
                http_response_code(201);
                echo json_encode(
                    [
                        "message" => "Product created",
                        "id" => $id
                    ]
                );
                break;
            default:
                http_response_code(405);
                header("allow: GET, POST");
        }
    }

    public function getValidationErrors(array $data): array
    {

        $errors = [];
        if (empty($data["name"])) {
            $errors[] = "Name is required";
        }
        if (array_key_exists("size", $data)) {
            if (filter_var($data["size"], FILTER_VALIDATE_INT) === false) {
                $errors[] = "size must be an integer";
            }
        }

        return $errors;
    }
}
