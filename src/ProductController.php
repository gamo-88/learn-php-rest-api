<?php

class ProductController
{
    public function __construct(private ProductGateway $gateway, private ImageServices $imageService) {}

    public function processRequest(string $method, ?string $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }
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
                break;

            case "PATCH":
                //Se souvenire que php ne prend pas les types form/data en corps deS requte PATCH  donc pour modifier une ressource il faut la passer en POST avant de la rediriger vers le bloc du code qui va la modifier comme fait dans la premiere condition de la fonction processRequest ou on evalu s'il ya un name(__method) et en fonction de sa presence on applique le bloc ci de patch


                //$data = (array) json_decode(file_get_contents("php://input"), true);
                $rawInput = file_get_contents("php://input");
                $data = json_decode($rawInput, true) ?? $_POST;
                // echo json_encode(["data1" => $data, "post" => $_POST]);


                $imageUrl = $product["imageUrl"] ?? null;

                if (isset($_FILES["image"]) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {

                    if ($imageUrl !== null && is_string($imageUrl)) {
                        $hasDelete = $this->imageService->deleteImage($imageUrl);
                    } else {
                        $hasDelete = true;
                    }

                    $imageUrl = $this->imageService->uploadImage($_FILES["image"]);

                    if ($imageUrl !== null && $hasDelete) {
                        $data["imageUrl"] = $imageUrl;
                    }
                }

                $errors = $this->getValidationErrors($data, false);

                if (!empty($errors)) {
                    http_response_code(422);
                    echo json_encode(["errors" => $errors]);
                    break;
                }

                $row = $this->gateway->update($product, $data);
                http_response_code(201);
                echo json_encode(
                    [
                        "message" => "Product updated",
                        "row" => $row
                    ]
                );
                /*              
  echo json_encode([
                    "method" => $_SERVER['REQUEST_METHOD'],
                    "content_type" => $_SERVER['CONTENT_TYPE'] ?? null,
                    "data" => $data,
                    "post" => $_POST,
                    "files" => $_FILES
                ]);
                */
                break;

            case "DELETE":
                $hasDeleted = $this->imageService->deleteImage($product["imageUrl"]);
                $rows = $this->gateway->delete($id);

                echo json_encode([
                    "message" => "Product $id deleted",
                    "rows" => $rows,
                    "hasDeletedImage" => $hasDeleted
                ]);
                break;

            default:
                http_response_code(405);
                header("Allow: GET, PATCH, DELETE");
        }
    }

    
    private function processCollectionRequest(string $method): void
    {
        switch ($method) {
            case "GET":
                echo json_encode($this->gateway->getAll());
                break;
            case "POST":
                //$data = (array) json_decode(file_get_contents("php://input"), true);
                $rawInput = file_get_contents("php://input");
                $data = json_decode($rawInput, true) ?? $_POST;

                $imageUrl = null;
                if (isset($_FILES["image"]) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $imageUrl = $this->imageService->uploadImage($_FILES["image"]);
                    if ($imageUrl !== null) {
                        $data["imageUrl"] = $imageUrl;
                    }
                }


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
                header("Allow: GET, POST");
        }
    }

    public function getValidationErrors(array $data, bool $is_new = true): array
    {

        $errors = [];
        if ($is_new && empty($data["name"])) {
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
