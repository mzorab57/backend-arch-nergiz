<?php
require_once __DIR__ . '/../models/Service.php';
require_once __DIR__ . '/../middleware/protect_admin.php';
require_once __DIR__ . '/../helpers/response.php';

class ServiceController {
    private $service;
    
    public function __construct($pdo) {
        $this->service = new Service($pdo);
    }
    
    /**
     * Handle GET requests
     */
    public function handleGet($id = null) {
        try {
            if ($id) {
                $serviceData = $this->service->getById($id);
                if (!$serviceData) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Service not found']);
                    return;
                }
                echo json_encode($serviceData);
            } else {
                $services = $this->service->all();
                echo json_encode($services);
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
        // Require admin access for service management
        $admin = protectAdmin();
        if (!$admin) {
            return;
        }
        
        try {
            // Validate required fields
            if (!isset($data['name']) || empty(trim($data['name']))) {
                http_response_code(400);
                echo json_encode(['error' => 'Service name is required']);
                return;
            }
            
            $name = trim($data['name']);
            $image = isset($data['image']) ? trim($data['image']) : null;
            $description = isset($data['description']) ? trim($data['description']) : null;
            
            $result = $this->service->create($name, $image, $description);
            
            if ($result) {
                http_response_code(201);
                echo json_encode([
                    'message' => 'Service created successfully',
                    'service_id' => $this->service->getLastInsertId()
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to create service']);
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
        // Require admin access for service management
        $admin = protectAdmin();
        if (!$admin) {
            return;
        }
        
        try {
            // Check if service exists
            $existingService = $this->service->getById($id);
            if (!$existingService) {
                http_response_code(404);
                echo json_encode(['error' => 'Service not found']);
                return;
            }
            
            // Validate required fields
            if (!isset($data['name']) || empty(trim($data['name']))) {
                http_response_code(400);
                echo json_encode(['error' => 'Service name is required']);
                return;
            }
            
            $name = trim($data['name']);
            $image = isset($data['image']) ? trim($data['image']) : null;
            $description = isset($data['description']) ? trim($data['description']) : null;
            
            $result = $this->service->update($id, $name, $image, $description);
            
            if ($result) {
                echo json_encode(['message' => 'Service updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to update service']);
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
        // Require admin access for service management
        $admin = protectAdmin();
        if (!$admin) {
            return;
        }
        
        try {
            // Check if service exists
            $existingService = $this->service->getById($id);
            if (!$existingService) {
                http_response_code(404);
                echo json_encode(['error' => 'Service not found']);
                return;
            }
            
            $result = $this->service->delete($id);
            
            if ($result) {
                echo json_encode(['message' => 'Service deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to delete service']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
        }
    }
}