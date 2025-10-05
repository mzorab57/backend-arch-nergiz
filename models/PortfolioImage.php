<?php
class PortfolioImage {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function all() {
        $stmt = $this->pdo->query("SELECT * FROM portfolio_images ORDER BY is_primary DESC, id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM portfolio_images WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByPortfolio($portfolio_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM portfolio_images WHERE portfolio_id = :portfolio_id ORDER BY is_primary DESC, id DESC");
        $stmt->execute(['portfolio_id' => $portfolio_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($portfolio_id, $image, $is_primary = false) {
        $stmt = $this->pdo->prepare("INSERT INTO portfolio_images (portfolio_id, image, is_primary) VALUES (:portfolio_id, :image, :is_primary)");
        return $stmt->execute([
            'portfolio_id' => $portfolio_id,
            'image' => $image,
            'is_primary' => $is_primary ? 1 : 0
        ]);
    }

    public function update($id, $portfolio_id, $image, $is_primary = false) {
        $stmt = $this->pdo->prepare("UPDATE portfolio_images SET portfolio_id = :portfolio_id, image = :image, is_primary = :is_primary WHERE id = :id");
        return $stmt->execute([
            'portfolio_id' => $portfolio_id,
            'image' => $image,
            'is_primary' => $is_primary ? 1 : 0,
            'id' => $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM portfolio_images WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function unsetPrimaryForPortfolio($portfolio_id) {
        $stmt = $this->pdo->prepare("UPDATE portfolio_images SET is_primary = 0 WHERE portfolio_id = :portfolio_id");
        return $stmt->execute(['portfolio_id' => $portfolio_id]);
    }

    public function getLastInsertId() {
        return $this->pdo->lastInsertId();
    }
}