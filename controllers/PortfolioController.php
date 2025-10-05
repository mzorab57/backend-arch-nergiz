<?php
require_once __DIR__ . '/../models/Portfolio.php';
require_once __DIR__ . '/../middleware/protect_admin.php';
require_once __DIR__ . '/../helpers/response.php';


class PortfolioController {
    private $portfolio;
    
    public function __construct($pdo) {
        $this->portfolio = new Portfolio($pdo);
    }
    
    /**
     * Handle GET requests
     */
    public function handleGet($id = null) {
        try {
            if ($id) {
                $portfolioData = $this->portfolio->getById($id);
                if (!$portfolioData) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Portfolio item not found']);
                    return;
                }
                echo json_encode($portfolioData);
            } else {
                // Check for query parameters
                $category_id = isset($_GET['category_id']) ? $_GET['category_id'] : null;
                $type = isset($_GET['type']) ? $_GET['type'] : null;
                
                if ($category_id) {
                    $portfolios = $this->portfolio->getByCategory($category_id);
                } elseif ($type) {
                    $portfolios = $this->portfolio->getByType($type);
                } else {
                    $portfolios = $this->portfolio->all();
                }
                
                echo json_encode($portfolios);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
        }
    }
    
    /**
     * Handle POST requests
     */
    public function handlePost($data) {
        // Require admin access for portfolio management
        $admin = protectAdmin();
        if (!$admin) {
            return;
        }
        
        try {
            // Validate required fields
            if (!isset($data['name']) || empty(trim($data['name']))) {
                http_response_code(400);
                echo json_encode(['error' => 'Portfolio name is required']);
                return;
            }
            
            if (!isset($data['type']) || empty(trim($data['type']))) {
                http_response_code(400);
                echo json_encode(['error' => 'Portfolio type is required']);
                return;
            }
            
            // Validate type enum
            $validTypes = ['interior', 'exterior'];
            if (!in_array($data['type'], $validTypes)) {
                http_response_code(400);
                echo json_encode(['error' => 'Portfolio type must be either "interior" or "exterior"']);
                return;
            }
            
            $name = trim($data['name']);
            $work = isset($data['work']) ? trim($data['work']) : null;
            $type = trim($data['type']);
            $description = isset($data['description']) ? trim($data['description']) : null;
            $date = isset($data['date']) ? $data['date'] : null;
            $category_id = isset($data['category_id']) ? $data['category_id'] : null;
            
            // Validate date format if provided
            if ($date && !DateTime::createFromFormat('Y-m-d', $date)) {
                http_response_code(400);
                echo json_encode(['error' => 'Date must be in YYYY-MM-DD format']);
                return;
            }
            
            $result = $this->portfolio->create($name, $work, $type, $description, $date, $category_id);
            
            if ($result) {
                http_response_code(201);
                echo json_encode([
                    'message' => 'Portfolio item created successfully',
                    'portfolio_id' => $this->portfolio->getLastInsertId()
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to create portfolio item']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
        }
    }
    
    /**
     * Handle PUT requests
     */
    public function handlePut($id, $data) {
        // Require admin access for portfolio management
        $admin = protectAdmin();
        if (!$admin) {
            return;
        }
        
        try {
            // Check if portfolio item exists
            $existingPortfolio = $this->portfolio->getById($id);
            if (!$existingPortfolio) {
                http_response_code(404);
                echo json_encode(['error' => 'Portfolio item not found']);
                return;
            }
            
            // Validate required fields
            if (!isset($data['name']) || empty(trim($data['name']))) {
                http_response_code(400);
                echo json_encode(['error' => 'Portfolio name is required']);
                return;
            }
            
            if (!isset($data['type']) || empty(trim($data['type']))) {
                http_response_code(400);
                echo json_encode(['error' => 'Portfolio type is required']);
                return;
            }
            
            // Validate type enum
            $validTypes = ['interior', 'exterior'];
            if (!in_array($data['type'], $validTypes)) {
                http_response_code(400);
                echo json_encode(['error' => 'Portfolio type must be either "interior" or "exterior"']);
                return;
            }
            
            $name = trim($data['name']);
            $work = isset($data['work']) ? trim($data['work']) : null;
            $type = trim($data['type']);
            $description = isset($data['description']) ? trim($data['description']) : null;
            $date = isset($data['date']) ? $data['date'] : null;
            $category_id = isset($data['category_id']) ? $data['category_id'] : null;
            
            // Validate date format if provided
            if ($date && !DateTime::createFromFormat('Y-m-d', $date)) {
                http_response_code(400);
                echo json_encode(['error' => 'Date must be in YYYY-MM-DD format']);
                return;
            }
            
            $result = $this->portfolio->update($id, $name, $work, $type, $description, $date, $category_id);
            
            if ($result) {
                echo json_encode(['message' => 'Portfolio item updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to update portfolio item']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
        }
    }
    
    /**
     * Handle DELETE requests
     */
    public function handleDelete($id) {
        // Require admin access for portfolio management
        $admin = protectAdmin();
        if (!$admin) {
            return;
        }
        
        try {
            // Check if portfolio item exists
            $existingPortfolio = $this->portfolio->getById($id);
            if (!$existingPortfolio) {
                http_response_code(404);
                echo json_encode(['error' => 'Portfolio item not found']);
                return;
            }
            
            $result = $this->portfolio->delete($id);
            
            if ($result) {
                echo json_encode(['message' => 'Portfolio item deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to delete portfolio item']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
        }
    }
}