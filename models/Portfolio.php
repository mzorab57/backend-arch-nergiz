<?php
class Portfolio {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function all() {
        $stmt = $this->pdo->query("SELECT p.*, c.name AS category_name FROM portfolio p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
        $portfolios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$portfolios) {
            return [];
        }
        $ids = array_column($portfolios, 'id');
        $imagesMap = $this->getImagesByPortfolioIds($ids);
        foreach ($portfolios as &$p) {
            $p['images'] = $imagesMap[$p['id']] ?? [];
        }
        return $portfolios;
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT p.*, c.name AS category_name FROM portfolio p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = :id");
        $stmt->execute(['id' => $id]);
        $portfolio = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$portfolio) {
            return null;
        }
        $imagesMap = $this->getImagesByPortfolioIds([$id]);
        $portfolio['images'] = $imagesMap[$id] ?? [];
        return $portfolio;
    }

    public function create($name, $work = null, $type, $description = null, $date = null, $category_id = null) {
        $stmt = $this->pdo->prepare("INSERT INTO portfolio (name, work, type, description, date, category_id) VALUES (:name, :work, :type, :description, :date, :category_id)");
        return $stmt->execute([
            'name' => $name,
            'work' => $work,
            'type' => $type,
            'description' => $description,
            'date' => $date,
            'category_id' => $category_id
        ]);
    }

    public function update($id, $name, $work = null, $type, $description = null, $date = null, $category_id = null) {
        $stmt = $this->pdo->prepare("UPDATE portfolio SET name = :name, work = :work, type = :type, description = :description, date = :date, category_id = :category_id WHERE id = :id");
        return $stmt->execute([
            'name' => $name,
            'work' => $work,
            'type' => $type,
            'description' => $description,
            'date' => $date,
            'category_id' => $category_id,
            'id' => $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM portfolio WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function getByCategory($category_id) {
        $stmt = $this->pdo->prepare("SELECT p.*, c.name AS category_name FROM portfolio p LEFT JOIN categories c ON p.category_id = c.id WHERE p.category_id = :category_id ORDER BY p.id DESC");
        $stmt->execute(['category_id' => $category_id]);
        $portfolios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$portfolios) {
            return [];
        }
        $ids = array_column($portfolios, 'id');
        $imagesMap = $this->getImagesByPortfolioIds($ids);
        foreach ($portfolios as &$p) {
            $p['images'] = $imagesMap[$p['id']] ?? [];
        }
        return $portfolios;
    }

    public function getByType($type) {
        $stmt = $this->pdo->prepare("SELECT p.*, c.name AS category_name FROM portfolio p LEFT JOIN categories c ON p.category_id = c.id WHERE p.type = :type ORDER BY p.id DESC");
        $stmt->execute(['type' => $type]);
        $portfolios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$portfolios) {
            return [];
        }
        $ids = array_column($portfolios, 'id');
        $imagesMap = $this->getImagesByPortfolioIds($ids);
        foreach ($portfolios as &$p) {
            $p['images'] = $imagesMap[$p['id']] ?? [];
        }
        return $portfolios;
    }

    public function getLastInsertId() {
        return $this->pdo->lastInsertId();
    }
    private function getImagesByPortfolioIds(array $ids) {
        if (empty($ids)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT id, portfolio_id, image, is_primary FROM portfolio_images WHERE portfolio_id IN ($placeholders) ORDER BY is_primary DESC, id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($ids);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $map = [];
        foreach ($rows as $row) {
            $pid = $row['portfolio_id'];
            if (!isset($map[$pid])) {
                $map[$pid] = [];
            }
            $map[$pid][] = [
                'id' => (int)$row['id'],
                'image' => $row['image'],
                'is_primary' => (bool)$row['is_primary']
            ];
        }
        return $map;
    }
}