<?php
class Contact {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function all() {
        $stmt = $this->pdo->query("SELECT * FROM contacts ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM contacts WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($address = null, $email = null, $phone = null) {
        $stmt = $this->pdo->prepare("INSERT INTO contacts (address, email, phone) VALUES (:address, :email, :phone)");
        return $stmt->execute([
            'address' => $address,
            'email' => $email,
            'phone' => $phone
        ]);
    }

    public function update($id, $address = null, $email = null, $phone = null) {
        $stmt = $this->pdo->prepare("UPDATE contacts SET address = :address, email = :email, phone = :phone WHERE id = :id");
        return $stmt->execute([
            'address' => $address,
            'email' => $email,
            'phone' => $phone,
            'id' => $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM contacts WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function getLastInsertId() {
        return $this->pdo->lastInsertId();
    }
}