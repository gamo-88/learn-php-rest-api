<?php

class ProductGateway
{

    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

    public function getAll(): array
    {
        $sql = "SELECT * FROM product";
        $stmt = $this->conn->query($sql);
        $data = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }
        return $data;
    }

    public function create(array $data): string
    {
        $sql = "INSERT INTO product (name, size, is_available, imageUrl) values (:name, :size, :is_available, :imageUrl)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":name", $data["name"], PDO::PARAM_STR);
        $stmt->bindValue(":size", $data["size"] ?? 0, PDO::PARAM_INT);
        $stmt->bindValue(":is_available", $data["is_available"] ?? false, PDO::PARAM_BOOL);
        $stmt->bindValue(":imageUrl", $data["imageUrl"] ?? null, PDO::PARAM_STR);


        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    public function get(string $id): array | false
    {
        $sql = "SELECT * FROM product WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data;
    }

    public function update(array $current, array $new): int
    {




        $sql = "UPDATE product SET name = :name, size = :size, is_available = :is_available, imageUrl = :imageUrl WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":name", $new["name"] ?? $current["name"], PDO::PARAM_STR);
        $stmt->bindValue(":size", $new["size"] ?? $current["size"], PDO::PARAM_INT);
        $stmt->bindValue(":is_available", $new["is_available"] ?? $current["is_available"], PDO::PARAM_BOOL);
        $stmt->bindValue(":id", $current["id"], PDO::PARAM_INT);


        $imageUrl = $new["imageUrl"] ?? $current["imageUrl"] ?? null;
        if ($imageUrl === null) {
            $stmt->bindValue(":imageUrl", null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(":imageUrl", $imageUrl, PDO::PARAM_STR);
        }

        $stmt->execute();

        return $stmt->rowCount();
    }

    public function delete(int $id)
    {

        $sql = "DELETE FROM product WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);

        $stmt->execute();


        return $id;
    }
}
