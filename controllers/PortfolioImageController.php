<?php
require_once __DIR__ . '/../models/PortfolioImage.php';
require_once __DIR__ . '/../middleware/protect_admin.php';
require_once __DIR__ . '/../helpers/response.php';

class PortfolioImageController {
    private $images;
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->images = new PortfolioImage($pdo);
    }

    public function handleGet($id = null) {
        try {
            if ($id) {
                $image = $this->images->getById($id);
                if (!$image) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Portfolio image not found']);
                    return;
                }
                echo json_encode($image);
            } else {
                $portfolio_id = isset($_GET['portfolio_id']) ? $_GET['portfolio_id'] : null;
                if ($portfolio_id) {
                    $items = $this->images->getByPortfolio($portfolio_id);
                } else {
                    $items = $this->images->all();
                }
                echo json_encode($items);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
        }
    }

    public function handlePost($data) {
        $admin = protectAdmin();
        if (!$admin) {
            return;
        }

        try {
            if (!isset($data['portfolio_id']) || !is_numeric($data['portfolio_id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'portfolio_id is required']);
                return;
            }
            $portfolio_id = (int)$data['portfolio_id'];

            // Accept either a single image string or an array of image strings
            $imagesList = [];
            if (isset($data['images']) && is_array($data['images'])) {
                foreach ($data['images'] as $img) {
                    if (is_string($img)) {
                        $trimmed = trim($img);
                        if ($trimmed !== '') {
                            $imagesList[] = $trimmed;
                        }
                    }
                }
            }
            if (isset($data['image']) && is_string($data['image'])) {
                $single = trim($data['image']);
                if ($single !== '') {
                    $imagesList[] = $single;
                }
            }

            if (empty($imagesList)) {
                http_response_code(400);
                echo json_encode(['error' => 'image is required (or provide images array)']);
                return;
            }

            // Determine primary image among the provided ones
            $primaryIndex = null;
            if (isset($data['primary_index']) && is_numeric($data['primary_index'])) {
                $idx = (int)$data['primary_index'];
                if ($idx >= 0 && $idx < count($imagesList)) {
                    $primaryIndex = $idx;
                }
            } else {
                // Backward compatibility: allow is_primary flag when only one image is provided
                $is_primary_flag = isset($data['is_primary']) ? (bool)$data['is_primary'] : false;
                if ($is_primary_flag && count($imagesList) === 1) {
                    $primaryIndex = 0;
                }
            }

            if ($primaryIndex !== null) {
                // Unset previous primary for this portfolio before setting the new one
                $this->images->unsetPrimaryForPortfolio($portfolio_id);
            }

            $createdIds = [];
            foreach ($imagesList as $i => $imgPath) {
                $isPrimary = ($primaryIndex !== null && $primaryIndex === $i);
                $result = $this->images->create($portfolio_id, $imgPath, $isPrimary);
                if (!$result) {
                    http_response_code(500);
                    echo json_encode(['error' => 'Failed to create portfolio image']);
                    return;
                }
                $createdIds[] = (int)$this->images->getLastInsertId();
            }

            http_response_code(201);
            echo json_encode([
                'message' => 'Portfolio image(s) created successfully',
                'ids' => $createdIds,
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
        }
    }

    public function handlePut($id, $data) {
        $admin = protectAdmin();
        if (!$admin) {
            return;
        }

        try {
            $existing = $this->images->getById($id);
            if (!$existing) {
                http_response_code(404);
                echo json_encode(['error' => 'Portfolio image not found']);
                return;
            }

            if (!isset($data['portfolio_id']) || !is_numeric($data['portfolio_id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'portfolio_id is required']);
                return;
            }
            if (!isset($data['image']) || empty(trim($data['image']))) {
                http_response_code(400);
                echo json_encode(['error' => 'image is required']);
                return;
            }

            $portfolio_id = (int)$data['portfolio_id'];
            $image = trim($data['image']);
            $is_primary = isset($data['is_primary']) ? (bool)$data['is_primary'] : false;

            if ($is_primary) {
                $this->images->unsetPrimaryForPortfolio($portfolio_id);
            }

            $result = $this->images->update($id, $portfolio_id, $image, $is_primary);
            if ($result) {
                echo json_encode(['message' => 'Portfolio image updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to update portfolio image']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
        }
    }

    public function handleDelete($id) {
        $admin = protectAdmin();
        if (!$admin) {
            return;
        }

        try {
            $existing = $this->images->getById($id);
            if (!$existing) {
                http_response_code(404);
                echo json_encode(['error' => 'Portfolio image not found']);
                return;
            }
            $result = $this->images->delete($id);
            if ($result) {
                echo json_encode(['message' => 'Portfolio image deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to delete portfolio image']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
        }
    }
}