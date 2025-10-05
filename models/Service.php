<?php
class Service {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function all() {
        $stmt = $this->pdo->query("SELECT * FROM services ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM services WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($name, $image = null, $description = null) {
        $stmt = $this->pdo->prepare("INSERT INTO services (name, image, description) VALUES (:name, :image, :description)");
        return $stmt->execute([
            'name' => $name,
            'image' => $image,
            'description' => $description
        ]);
    }

    public function update($id, $name, $image = null, $description = null) {
        $stmt = $this->pdo->prepare("UPDATE services SET name = :name, image = :image, description = :description WHERE id = :id");
        return $stmt->execute([
            'name' => $name,
            'image' => $image,
            'description' => $description,
            'id' => $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM services WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function getLastInsertId() {
        return $this->pdo->lastInsertId();
    }
}